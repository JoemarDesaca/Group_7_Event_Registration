<?php

use App\Models\Event;
use App\Models\Registration;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function makeAdmin(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    return $admin->refresh();
}

it('shows each role only its own navigation and blocks attendee admin access', function () {
    $admin = makeAdmin();
    $attendee = User::factory()->create();

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()->assertSee('Create Event')->assertSee('Ticketing')->assertSee('Attendance')->assertSee('User Management');
    $this->actingAs($admin)->get(route('admin.events.index'))->assertOk()->assertSee('Event management');
    $this->actingAs($admin)->get(route('user.index'))->assertOk()->assertSee('List of Users');
    $this->actingAs($attendee)->get(route('dashboard'))
        ->assertOk()->assertSee('Browse Events')->assertSee('Events')->assertDontSee('Create Event')->assertDontSee('User Management');
    $this->actingAs($attendee)->get(route('admin.events.index'))->assertForbidden();
    $this->actingAs($attendee)->get(route('admin.ticketing.index'))->assertForbidden();
    $this->actingAs($attendee)->get(route('admin.attendance.index'))->assertForbidden();
    $this->actingAs($attendee)->get(route('user.index'))->assertForbidden();
});

it('redirects successful login to the correct role dashboard', function () {
    $admin = makeAdmin();
    $attendee = User::factory()->create();

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));
    auth()->logout();
    $this->post(route('login'), ['email' => $attendee->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
});

it('keeps public signups as attendees and allows only admins to provision admin accounts', function () {
    $this->post(route('register'), [
        'name' => 'Self Promoted', 'email' => 'self@example.test', 'password' => 'password123',
        'password_confirmation' => 'password123', 'role' => 'admin',
    ])->assertRedirect(route('dashboard'));
    expect(User::query()->where('email', 'self@example.test')->value('role'))->toBe('attendee');

    $attendee = User::factory()->create();
    $this->actingAs($attendee)->post(route('users.store'), [])->assertForbidden();

    $admin = makeAdmin();
    $this->actingAs($admin)->post(route('users.store'), [
        'fullname' => 'Provisioned Admin', 'email' => 'provisioned@example.test',
        'password' => 'password123', 'role' => 'admin',
    ])->assertRedirect(route('user.index'));
    expect(User::query()->where('email', 'provisioned@example.test')->value('role'))->toBe('admin');
});

it('connects admin event publication to attendee checkout, ticketing, and one-time attendance', function () {
    $admin = makeAdmin();
    $attendee = User::factory()->create();
    $startsAt = now()->addMonth();

    $this->actingAs($admin)->post(route('admin.events.store'), [
        'title' => 'Shared Data Showcase', 'description' => 'An event shared by the admin and attendee experiences.',
        'venue' => 'Community Hall', 'starts_at' => $startsAt->format('Y-m-d H:i:s'),
        'ends_at' => $startsAt->copy()->addHours(2)->format('Y-m-d H:i:s'),
        'registration_opens_at' => now()->subDay()->format('Y-m-d H:i:s'),
        'registration_closes_at' => $startsAt->copy()->subDay()->format('Y-m-d H:i:s'),
        'currency' => 'PHP', 'publish' => 1,
        'tickets' => [[
            'name' => 'General admission', 'description' => 'Entry', 'price' => '0.00', 'capacity' => 20,
            'minimum_per_order' => 1, 'maximum_per_order' => 5,
        ]],
    ])->assertRedirect(route('admin.events.index'));

    $event = Event::query()->where('title', 'Shared Data Showcase')->firstOrFail();
    $ticket = $event->ticketTypes()->firstOrFail();
    $this->actingAs($attendee)->get(route('events.index'))->assertOk()->assertSee('Shared Data Showcase')->assertSee('General admission');
    $this->actingAs($attendee)->post(route('registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => $attendee->name, 'attendee_email' => $attendee->email,
        'tickets' => [$ticket->id => 1],
    ])->assertRedirect();

    $registration = Registration::query()->with('tickets')->firstOrFail();
    $issuedTicket = $registration->tickets->firstOrFail();
    expect($registration->status)->toBe('confirmed')->and($issuedTicket->status)->toBe('valid');

    $this->actingAs($admin)->get(route('admin.ticketing.index'))
        ->assertOk()->assertSee($registration->reference)->assertSee($attendee->email)->assertSee('Shared Data Showcase');
    $this->actingAs($admin)->get(route('admin.attendance.index'))
        ->assertOk()->assertSee($issuedTicket->reference)->assertSee($attendee->name);
    $this->from(route('admin.attendance.index'))->post(route('admin.attendance.check-in', $issuedTicket))
        ->assertRedirect()->assertSessionHas('status');
    expect($issuedTicket->fresh()->status)->toBe('checked_in')->and($issuedTicket->fresh()->checked_in_at)->not->toBeNull();

    $this->from(route('admin.attendance.index'))->post(route('admin.attendance.check-in', $issuedTicket))
        ->assertRedirect()->assertSessionHasErrors('ticket');
});

it('hides draft, closed, cancelled, and past events from attendee discovery', function () {
    $attendee = User::factory()->create();
    auth()->login($attendee);
    $eligible = Event::factory()->create(['title' => 'Eligible event']);
    TicketType::factory()->create(['event_id' => $eligible->id]);
    Event::factory()->create(['title' => 'Draft event', 'status' => 'draft']);
    Event::factory()->create(['title' => 'Cancelled event', 'status' => 'cancelled']);
    Event::factory()->create(['title' => 'Past event', 'starts_at' => now()->subDay()]);
    Event::factory()->create(['title' => 'Closed registration', 'registration_closes_at' => now()->subMinute()]);

    $this->get(route('events.index'))->assertOk()->assertSee('Eligible event')
        ->assertDontSee('Draft event')->assertDontSee('Cancelled event')->assertDontSee('Past event')->assertDontSee('Closed registration');
});

it('does not allow invalid, unpaid, or cancelled-event tickets to be checked in', function () {
    $admin = makeAdmin();
    $attendee = User::factory()->create();
    $event = Event::factory()->create(['status' => 'cancelled']);
    $registration = Registration::factory()->create([
        'user_id' => $attendee->id, 'event_id' => $event->id, 'status' => 'confirmed',
        'reference' => 'REG-CANCELLED-TEST', 'attendee_name' => $attendee->name,
        'attendee_email' => $attendee->email, 'ticket_subtotal' => '0.00', 'add_on_subtotal' => '0.00',
        'discount_amount' => '0.00', 'total' => '0.00',
    ]);
    $item = $registration->items()->create([
        'ticket_name' => 'Admission', 'quantity' => 1, 'unit_price' => '0.00', 'line_total' => '0.00',
    ]);
    $issuedTicket = $registration->tickets()->create([
        'registration_item_id' => $item->id, 'reference' => 'TKT-CANCELLED-TEST', 'status' => 'valid',
    ]);

    $this->actingAs($admin)->from(route('admin.attendance.index'))
        ->post(route('admin.attendance.check-in', $issuedTicket))->assertSessionHasErrors('ticket');
    expect($issuedTicket->fresh()->status)->toBe('valid');
    expect(DB::table('issued_tickets')->where('id', $issuedTicket->id)->value('checked_in_at'))->toBeNull();
});
