<?php

namespace App\Services;

use App\Models\AddOn;
use App\Models\DiscountCode;
use App\Models\Event;
use App\Models\IssuedTicket;
use App\Models\Registration;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /** @param array<int, int> $ticketQuantities @param array<int, int> $addOnQuantities @return array{ticket_subtotal: int, add_on_subtotal: int, discount_amount: int, total: int} */
    public function quote(Event $event, array $ticketQuantities, array $addOnQuantities, ?string $code): array
    {
        $tickets = $this->selectedRows($event->ticketTypes()->where('is_active', true)->get(), $ticketQuantities, 'ticket');
        $addOns = $this->selectedRows($event->addOns()->where('is_active', true)->get(), $addOnQuantities, 'add-on');
        $ticketSubtotal = $this->subtotal($tickets, $ticketQuantities);
        $addOnSubtotal = $this->subtotal($addOns, $addOnQuantities);
        $discount = $code ? $this->discountFor($code, $event, $ticketSubtotal + $addOnSubtotal) : null;
        $discountAmount = $discount ? $this->discountAmount($discount, $ticketSubtotal + $addOnSubtotal) : 0;

        return ['ticket_subtotal' => $ticketSubtotal, 'add_on_subtotal' => $addOnSubtotal, 'discount_amount' => $discountAmount, 'total' => max(0, $ticketSubtotal + $addOnSubtotal - $discountAmount)];
    }

    /** @param array<int, int> $ticketQuantities @param array<int, int> $addOnQuantities */
    public function register(User $user, Event $event, array $ticketQuantities, array $addOnQuantities, ?string $code, string $attendeeName, string $attendeeEmail): Registration
    {
        return DB::transaction(function () use ($user, $event, $ticketQuantities, $addOnQuantities, $code, $attendeeName, $attendeeEmail): Registration {
            $event = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            if (! $event->isAcceptingRegistrations()) {
                throw ValidationException::withMessages(['event_id' => 'This event is no longer accepting registrations.']);
            }

            $tickets = $this->lockedSelections($event->ticketTypes(), $ticketQuantities, 'ticket');
            $addOns = $this->lockedSelections($event->addOns(), $addOnQuantities, 'add-on');
            $ticketSubtotal = $this->subtotal($tickets, $ticketQuantities);
            $addOnSubtotal = $this->subtotal($addOns, $addOnQuantities);
            $discount = $code ? $this->discountFor($code, $event, $ticketSubtotal + $addOnSubtotal, true) : null;
            $discountAmount = $discount ? $this->discountAmount($discount, $ticketSubtotal + $addOnSubtotal) : 0;
            $total = max(0, $ticketSubtotal + $addOnSubtotal - $discountAmount);
            $registration = Registration::query()->create([
                'user_id' => $user->id, 'event_id' => $event->id, 'discount_code_id' => $discount?->id,
                'reference' => 'EVT-'.Str::upper(Str::random(18)), 'attendee_name' => $attendeeName, 'attendee_email' => $attendeeEmail,
                'ticket_subtotal' => $this->fromCents($ticketSubtotal), 'add_on_subtotal' => $this->fromCents($addOnSubtotal),
                'discount_amount' => $this->fromCents($discountAmount), 'total' => $this->fromCents($total),
                'status' => $total === 0 ? 'confirmed' : 'pending_payment',
            ]);

            foreach ($tickets as $ticket) {
                $quantity = $ticketQuantities[$ticket->id];
                $item = $registration->items()->create([
                    'ticket_type_id' => $ticket->id, 'ticket_name' => $ticket->name, 'quantity' => $quantity,
                    'unit_price' => $ticket->price, 'line_total' => $this->fromCents($this->toCents((string) $ticket->price) * $quantity),
                ]);
                $ticket->increment('sold', $quantity);
                for ($index = 0; $index < $quantity; $index++) {
                    IssuedTicket::query()->create([
                        'registration_id' => $registration->id, 'registration_item_id' => $item->id,
                        'reference' => 'TKT-'.Str::upper(Str::random(18)), 'status' => $total === 0 ? 'valid' : 'pending_payment',
                    ]);
                }
            }

            foreach ($addOns as $addOn) {
                $quantity = $addOnQuantities[$addOn->id];
                $registration->addOns()->create([
                    'add_on_id' => $addOn->id, 'add_on_name' => $addOn->name, 'quantity' => $quantity,
                    'unit_price' => $addOn->price, 'line_total' => $this->fromCents($this->toCents((string) $addOn->price) * $quantity),
                ]);
                $addOn->increment('sold', $quantity);
            }

            if ($discount) {
                $discount->increment('redemption_count');
                $discount->redemptions()->create(['registration_id' => $registration->id]);
            }

            return $registration;
        }, 3);
    }

    /** @param Collection<int, TicketType|AddOn> $rows @param array<int, int> $quantities */
    private function subtotal(Collection $rows, array $quantities): int
    {
        return $rows->sum(fn (TicketType|AddOn $row): int => $this->toCents((string) $row->price) * ($quantities[$row->id] ?? 0));
    }

    /** @param Collection<int, TicketType|AddOn> $rows @param array<int, int> $quantities @return Collection<int, TicketType|AddOn> */
    private function selectedRows(Collection $rows, array $quantities, string $label): Collection
    {
        $selected = $rows->filter(fn (TicketType|AddOn $row): bool => ($quantities[$row->id] ?? 0) > 0);
        if ($label === 'ticket' && $selected->isEmpty()) {
            throw ValidationException::withMessages(['tickets' => 'Select at least one ticket to register.']);
        }
        $selectedIds = $selected->modelKeys();
        $requestedIds = collect($quantities)->filter(fn (int $quantity): bool => $quantity > 0)->keys()->map(fn ($id): int => (int) $id)->all();
        if (count(array_diff($requestedIds, $selectedIds)) > 0) {
            throw ValidationException::withMessages([$label === 'ticket' ? 'tickets' : 'add_ons' => 'One or more selections are no longer available.']);
        }
        $this->assertLimits($selected, $quantities, $label, true);

        return $selected;
    }

    private function lockedSelections($relation, array $quantities, string $label): Collection
    {
        $ids = collect($quantities)->filter(fn (int $quantity): bool => $quantity > 0)->keys()->map(fn ($id): int => (int) $id)->sort()->values();
        if ($label === 'ticket' && $ids->isEmpty()) {
            throw ValidationException::withMessages(['tickets' => 'Select at least one ticket to register.']);
        }
        $rows = $relation->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        if ($rows->count() !== $ids->count() || $rows->contains(fn ($row): bool => ! $row->is_active)) {
            throw ValidationException::withMessages([$label === 'ticket' ? 'tickets' : 'add_ons' => 'One or more selections are no longer available.']);
        }
        $this->assertLimits($rows, $quantities, $label, true);

        return $rows;
    }

    /** @param Collection<int, TicketType|AddOn> $rows @param array<int, int> $quantities */
    private function assertLimits(Collection $rows, array $quantities, string $label, bool $checkInventory): void
    {
        foreach ($rows as $row) {
            $quantity = $quantities[$row->id] ?? 0;
            if ($label === 'ticket') {
                if ($quantity < $row->minimum_per_order || $quantity > $row->maximum_per_order) {
                    throw ValidationException::withMessages(['tickets' => "Choose between {$row->minimum_per_order} and {$row->maximum_per_order} of {$row->name}."]);
                }
                if ($checkInventory && $quantity > $row->capacity - $row->sold) {
                    throw ValidationException::withMessages(['tickets' => "There are not enough {$row->name} tickets remaining."]);
                }
            } elseif ($quantity > 10 || ($checkInventory && $row->capacity !== null && $quantity > $row->capacity - $row->sold)) {
                throw ValidationException::withMessages(['add_ons' => "The selected quantity for {$row->name} is unavailable."]);
            }
        }
    }

    private function discountFor(string $code, Event $event, int $subtotal, bool $lock = false): ?DiscountCode
    {
        $query = DiscountCode::query()->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))]);
        if ($lock) {
            $query->orderBy('id')->lockForUpdate();
        }
        $discount = $query->first();
        if (! $discount || ! $discount->is_active || ($discount->event_id && $discount->event_id !== $event->id)
            || ($discount->starts_at && $discount->starts_at->isFuture()) || ($discount->expires_at && $discount->expires_at->isPast())
            || ($discount->maximum_redemptions !== null && $discount->redemption_count >= $discount->maximum_redemptions)
            || $subtotal < $this->toCents((string) $discount->minimum_order)
            || ($discount->type === 'percentage' && $this->toCents((string) $discount->amount) > 10000)) {
            throw ValidationException::withMessages(['discount_code' => 'This discount code is invalid or unavailable for this order.']);
        }

        return $discount;
    }

    private function discountAmount(DiscountCode $discount, int $subtotal): int
    {
        $amount = $this->toCents((string) $discount->amount);

        return min($subtotal, $discount->type === 'percentage' ? intdiv($subtotal * $amount, 10000) : $amount);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
