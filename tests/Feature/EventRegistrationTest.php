<?php

use App\Models\AddOn;
use App\Models\DiscountCode;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function openEvent(array $attributes = []): Event
{
    return Event::factory()->create($attributes);
}

function loginAttendee(): User
{
    $user = User::factory()->create();
    auth()->login($user);

    return $user;
}

it('shows only published events that are accepting registrations', function () {
    loginAttendee();
    $openEvent = openEvent(['title' => 'Open Forum']);
    TicketType::factory()->create(['event_id' => $openEvent->id]);
    openEvent(['title' => 'Draft Gathering', 'status' => 'draft']);
    openEvent(['title' => 'Closed Gathering', 'registration_closes_at' => now()->subMinute()]);
    openEvent(['title' => 'Past Gathering', 'starts_at' => now()->subDay(), 'registration_closes_at' => null]);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSee('Open Forum')
        ->assertDontSee('Draft Gathering')
        ->assertDontSee('Closed Gathering')
        ->assertDontSee('Past Gathering')
        ->assertSee((string) $openEvent->id);
    $this->get(route('events.index', ['event' => 999999]))->assertNotFound();
});

it('stores multiple ticket types, add-ons, exact totals and individual pending tickets', function () {
    $attendee = loginAttendee();
    $event = openEvent();
    $general = TicketType::factory()->create(['event_id' => $event->id, 'name' => 'General', 'price' => '100.00', 'capacity' => 5]);
    $vip = TicketType::factory()->create(['event_id' => $event->id, 'name' => 'VIP', 'price' => '250.00', 'capacity' => 3]);
    $meal = AddOn::factory()->create(['event_id' => $event->id, 'price' => '25.00', 'capacity' => 4]);

    $response = $this->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$general->id => 2, $vip->id => 1], 'add_ons' => [$meal->id => 1], 'prices' => [$general->id => '0.01'],
    ]);
    $response->assertRedirect();

    $registration = $attendee->registrations()->with(['items', 'tickets'])->firstOrFail();
    expect($registration->ticket_subtotal)->toBe('450.00')
        ->and($registration->add_on_subtotal)->toBe('25.00')
        ->and($registration->total)->toBe('475.00')
        ->and($registration->status)->toBe('pending_payment')
        ->and($registration->tickets)->toHaveCount(3)
        ->and($registration->tickets->pluck('reference')->unique())->toHaveCount(3)
        ->and($registration->tickets->every(fn ($ticket): bool => $ticket->status === 'pending_payment'))->toBeTrue();
    expect($general->fresh()->sold)->toBe(2)->and($vip->fresh()->sold)->toBe(1)->and($meal->fresh()->sold)->toBe(1);
    $this->get(route('registrations.show', $registration))->assertOk()->assertSee($registration->reference)->assertSee('Payment is pending');
});

it('rejects sold-out and inactive ticket selections without changing inventory', function () {
    loginAttendee();
    $event = openEvent();
    $soldOut = TicketType::factory()->create(['event_id' => $event->id, 'capacity' => 1, 'sold' => 1]);
    $inactive = TicketType::factory()->create(['event_id' => $event->id, 'is_active' => false]);

    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$soldOut->id => 1],
    ])->assertSessionHasErrors('tickets');

    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$inactive->id => 1],
    ])->assertSessionHasErrors('tickets');

    $otherEvent = openEvent();
    $otherEventTicket = TicketType::factory()->create(['event_id' => $otherEvent->id]);
    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$otherEventTicket->id => 1],
    ])->assertSessionHasErrors('tickets');

    $available = TicketType::factory()->create(['event_id' => $event->id, 'capacity' => 20, 'maximum_per_order' => 3]);
    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$available->id => 4],
    ])->assertSessionHasErrors('tickets');

    expect($event->registrations()->count())->toBe(0)->and($soldOut->fresh()->sold)->toBe(1);
});

it('applies and records a discount under its redemption limit', function () {
    $attendee = loginAttendee();
    $event = openEvent();
    $ticket = TicketType::factory()->create(['event_id' => $event->id, 'price' => '200.00']);
    $code = DiscountCode::query()->create([
        'event_id' => $event->id, 'code' => 'SAVE10', 'type' => 'percentage', 'amount' => '10.00',
        'minimum_order' => '100.00', 'maximum_redemptions' => 1, 'is_active' => true,
    ]);
    DiscountCode::query()->create([
        'event_id' => $event->id, 'code' => 'PAST', 'type' => 'fixed', 'amount' => '25.00',
        'expires_at' => now()->subMinute(), 'is_active' => true,
    ]);

    $this->post(route('registrations.discount-preview'), [
        'event_id' => $event->id, 'tickets' => [$ticket->id => 1], 'discount_code' => 'SAVE10',
    ])->assertOk()->assertJson(['discount_amount' => '20.00', 'total' => '180.00', 'currency' => $event->currency]);

    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$ticket->id => 1], 'discount_code' => 'PAST',
    ])->assertSessionHasErrors('discount_code');

    $this->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$ticket->id => 1], 'discount_code' => 'save10',
    ])->assertRedirect();

    $registration = $attendee->registrations()->firstOrFail();
    expect($registration->discount_amount)->toBe('20.00')
        ->and($registration->total)->toBe('180.00')
        ->and($code->fresh()->redemption_count)->toBe(1)
        ->and(DB::table('discount_redemptions')->where('registration_id', $registration->id)->exists())->toBeTrue();

    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Another Attendee', 'attendee_email' => 'other@example.test',
        'tickets' => [$ticket->id => 1], 'discount_code' => 'SAVE10',
    ])->assertSessionHasErrors('discount_code');
    expect($code->fresh()->redemption_count)->toBe(1);
});

it('enforces add-on inventory and confirms zero-priced registrations', function () {
    $attendee = loginAttendee();
    $event = openEvent();
    $ticket = TicketType::factory()->create(['event_id' => $event->id, 'price' => '0.00', 'capacity' => 3]);
    $addOn = AddOn::factory()->create(['event_id' => $event->id, 'capacity' => 1, 'sold' => 1]);

    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$ticket->id => 1], 'add_ons' => [$addOn->id => 1],
    ])->assertSessionHasErrors('add_ons');
    expect($attendee->registrations()->count())->toBe(0);

    $this->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$ticket->id => 1],
    ])->assertRedirect();
    $registration = $attendee->registrations()->with('tickets')->firstOrFail();
    expect($registration->total)->toBe('0.00')->and($registration->status)->toBe('confirmed')
        ->and($registration->tickets)->toHaveCount(1)->and($registration->tickets->first()->status)->toBe('valid');
});

it('rejects unavailable events, protects private registrations and gates organizer routes', function () {
    $attendee = loginAttendee();
    $event = openEvent(['registration_closes_at' => now()->subMinute()]);
    $ticket = TicketType::factory()->create(['event_id' => $event->id]);

    $this->from(route('events.index'))->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'Alex Attendee', 'attendee_email' => 'alex@example.test',
        'tickets' => [$ticket->id => 1],
    ])->assertSessionHasErrors('event_id');
    $this->post(route('admin.events.store'), [])->assertForbidden();

    $organizer = User::factory()->create();
    DB::table('users')->where('id', $organizer->id)->update(['role' => 'admin']);
    $organizer->refresh();
    $this->actingAs($organizer)->get(route('admin.events.index'))->assertOk();
    $startsAt = now()->addMonth();
    $this->post(route('admin.events.store'), [
        'title' => 'Admin-created showcase', 'description' => 'A real event published from the admin workspace.',
        'venue' => 'Community Hall', 'starts_at' => $startsAt->format('Y-m-d H:i:s'),
        'ends_at' => $startsAt->copy()->addHours(2)->format('Y-m-d H:i:s'),
        'registration_opens_at' => now()->subDay()->format('Y-m-d H:i:s'),
        'registration_closes_at' => $startsAt->copy()->subDay()->format('Y-m-d H:i:s'), 'currency' => 'PHP', 'publish' => 1,
        'tickets' => [['name' => 'Admission', 'description' => 'Standard entry', 'price' => '0.00', 'capacity' => 20, 'minimum_per_order' => 1, 'maximum_per_order' => 5]],
    ])->assertRedirect(route('admin.events.index'));
    expect(Event::query()->where('title', 'Admin-created showcase')->where('organizer_id', $organizer->id)->exists())->toBeTrue();
    $this->actingAs($attendee)->get(route('events.index'))->assertOk()->assertSee('Admin-created showcase');
    $this->actingAs($attendee)->get(route('admin.events.index'))->assertForbidden();
    $this->actingAs($organizer)->get(route('registrations.show', $attendee->registrations()->create([
        'event_id' => openEvent()->id, 'attendee_name' => $attendee->name, 'attendee_email' => $attendee->email,
        'reference' => 'EVT-PRIVATE-REFERENCE', 'ticket_subtotal' => '0.00', 'add_on_subtotal' => '0.00',
        'discount_amount' => '0.00', 'total' => '0.00', 'status' => 'confirmed',
    ])))->assertForbidden();
});

it('requires authentication for event discovery and checkout', function () {
    $this->get(route('events.index'))->assertRedirect(route('login'));
    $this->post(route('registrations.store'), [])->assertRedirect(route('login'));
});
