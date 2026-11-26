<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\DiscountCode;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $events = Event::query()->with(['ticketTypes', 'addOns', 'discountCodes'])->latest()->get();

        return view('admin.events.index', compact('events'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'description' => ['required', 'string', 'max:10000'],
            'venue' => ['required', 'string', 'max:255'], 'starts_at' => ['required', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'registration_opens_at' => ['nullable', 'date'], 'registration_closes_at' => ['nullable', 'date', 'after:registration_opens_at'],
            'currency' => ['required', 'string', 'size:3'], 'cover_image' => ['nullable', 'image', 'max:5120'],
            'publish' => ['nullable', 'boolean'],
            'tickets' => ['required', 'array', 'min:1'], 'tickets.*.name' => ['required', 'string', 'max:120'],
            'tickets.*.description' => ['nullable', 'string', 'max:1000'], 'tickets.*.price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'tickets.*.capacity' => ['required', 'integer', 'min:1'], 'tickets.*.minimum_per_order' => ['required', 'integer', 'min:1', 'max:10'],
            'tickets.*.maximum_per_order' => ['required', 'integer', 'min:1', 'max:10'],
            'add_ons' => ['nullable', 'array'], 'add_ons.*.name' => ['required', 'string', 'max:120'],
            'add_ons.*.description' => ['nullable', 'string', 'max:1000'], 'add_ons.*.price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'add_ons.*.capacity' => ['nullable', 'integer', 'min:1'],
        ]);

        foreach ($data['tickets'] as $index => $ticket) {
            if ($ticket['maximum_per_order'] < $ticket['minimum_per_order'] || $ticket['capacity'] < $ticket['minimum_per_order']) {
                return back()->withErrors(["tickets.$index.maximum_per_order" => 'The ticket capacity and maximum per order must support the minimum quantity.'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $data): void {
            $event = Event::query()->create([
                'organizer_id' => $request->user()->id, 'title' => $data['title'], 'description' => $data['description'],
                'venue' => $data['venue'], 'starts_at' => $data['starts_at'], 'ends_at' => $data['ends_at'] ?? null,
                'registration_opens_at' => $data['registration_opens_at'] ?? null, 'registration_closes_at' => $data['registration_closes_at'] ?? null,
                'currency' => Str::upper($data['currency']), 'status' => $request->boolean('publish') ? 'published' : 'draft',
                'cover_image' => $request->file('cover_image')?->store('event-covers', 'public'),
            ]);
            foreach ($data['tickets'] as $ticket) {
                $event->ticketTypes()->create($ticket);
            }
            foreach ($data['add_ons'] ?? [] as $addOn) {
                $event->addOns()->create($addOn);
            }
        });

        return redirect()->route('admin.events.index')->with('status', 'Event created successfully.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'description' => ['required', 'string', 'max:10000'],
            'venue' => ['required', 'string', 'max:255'], 'starts_at' => ['required', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'registration_opens_at' => ['nullable', 'date'], 'registration_closes_at' => ['nullable', 'date', 'after:registration_opens_at'],
            'currency' => ['required', 'string', 'size:3'], 'status' => ['required', Rule::in(['draft', 'published', 'closed', 'cancelled'])],
            'cover_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $data['currency'] = Str::upper($data['currency']);
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('event-covers', 'public');
        }
        $event->update($data);

        return back()->with('status', 'Event updated successfully.');
    }

    public function storeTicket(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'], 'capacity' => ['required', 'integer', 'min:1'],
            'minimum_per_order' => ['required', 'integer', 'min:1', 'max:10'], 'maximum_per_order' => ['required', 'integer', 'min:1', 'max:10'],
        ]);
        if ($data['maximum_per_order'] < $data['minimum_per_order'] || $data['capacity'] < $data['minimum_per_order']) {
            return back()->withErrors(['maximum_per_order' => 'The ticket capacity and maximum per order must support the minimum quantity.'])->withInput();
        }
        $event->ticketTypes()->create($data);

        return back()->with('status', 'Ticket type added.');
    }

    public function updateTicket(Request $request, Event $event, TicketType $ticket): RedirectResponse
    {
        $this->authorizeEvent($request, $event);
        abort_unless($ticket->event_id === $event->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'capacity' => ['required', 'integer', 'min:'.$ticket->sold],
            'minimum_per_order' => ['required', 'integer', 'min:1', 'max:10'],
            'maximum_per_order' => ['required', 'integer', 'min:1', 'max:10'], 'is_active' => ['required', 'boolean'],
        ]);
        if ($data['maximum_per_order'] < $data['minimum_per_order'] || ($data['is_active'] && $data['minimum_per_order'] > $data['capacity'] - $ticket->sold)) {
            return back()->withErrors(['maximum_per_order' => 'The remaining capacity and maximum per order must support the minimum quantity.'])->withInput();
        }
        $ticket->update($data);

        return back()->with('status', 'Ticket type updated.');
    }

    public function updateAddOn(Request $request, Event $event, AddOn $addOn): RedirectResponse
    {
        $this->authorizeEvent($request, $event);
        abort_unless($addOn->event_id === $event->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'capacity' => ['nullable', 'integer', 'min:'.$addOn->sold], 'is_active' => ['required', 'boolean'],
        ]);
        $addOn->update($data);

        return back()->with('status', 'Add-on updated.');
    }

    public function storeAddOn(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'], 'capacity' => ['nullable', 'integer', 'min:1'],
        ]);
        $event->addOns()->create($data);

        return back()->with('status', 'Add-on added.');
    }

    public function storeDiscount(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($request, $event);
        if (is_string($request->input('code'))) {
            $request->merge(['code' => Str::upper(trim($request->input('code')))]);
        }
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:discount_codes,code'],
            'type' => ['required', Rule::in(['fixed', 'percentage'])], 'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'minimum_order' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'], 'maximum_redemptions' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date', 'after:starts_at'],
        ]);
        if ($data['type'] === 'percentage' && $this->toCents((string) $data['amount']) > 10000) {
            return back()->withErrors(['amount' => 'Percentage discounts cannot exceed 100%.'])->withInput();
        }
        DiscountCode::query()->create([
            ...$data, 'event_id' => $event->id, 'code' => Str::upper($data['code']),
            'minimum_order' => $data['minimum_order'] ?? 0, 'is_active' => true,
        ]);

        return back()->with('status', 'Discount code created.');
    }

    public function updateDiscount(Request $request, Event $event, DiscountCode $discountCode): RedirectResponse
    {
        $this->authorizeEvent($request, $event);
        abort_unless($discountCode->event_id === $event->id, 404);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $discountCode->update($data);

        return back()->with('status', 'Discount code updated.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }

    private function authorizeEvent(Request $request, Event $event): void
    {
        $this->authorizeAdmin($request);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
