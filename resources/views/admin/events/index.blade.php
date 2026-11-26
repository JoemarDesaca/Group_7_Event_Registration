@extends('layouts.master')

@section('title', 'Organizer events')

@section('content')
<style>
    .organizer-page { max-width: 1180px; margin: 24px auto 56px; padding: 0 20px; }
    .organizer-page .page-intro, .event-admin-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; }
    .organizer-page .page-intro h1 { margin: 0; font-size: 28px; font-weight: 700; }
    .organizer-page .page-intro p, .event-admin-top p { color: #7b8191; }
    .organizer-card, .managed-event { margin-top: 18px; padding: 22px; border: 1px solid #e6e8eb; border-radius: 8px; background: white; }
    .organizer-form { display: grid; gap: 12px; }
    .organizer-form label { display: grid; gap: 6px; }
    .organizer-form .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .organizer-form fieldset { padding: 14px; border: 1px solid #e6e8eb; border-radius: 6px; }
    .organizer-form .checkbox-label { display: flex; align-items: center; gap: 8px; }
    .organizer-form .button, .organizer-page .button { display: inline-flex; width: fit-content; padding: 9px 14px; border: 0; border-radius: 5px; cursor: pointer; }
    .button-primary { color: white; background: #6259ca; }
    .button-quiet { color: #514d66; background: #f0eff8; }
    .status-pill { padding: 5px 10px; border-radius: 999px; background: #eeeefa; text-transform: capitalize; }
    .ticket-list, .add-on-list, .discount-list { margin: 12px 0; padding-left: 20px; }
    .empty-events { padding: 24px; text-align: center; }
    @media (max-width: 700px) { .organizer-form .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="organizer-page">
    <div class="page-intro"><div><p>ADMIN WORKSPACE</p><h1>Event management</h1><p>Create events, configure tickets, and manage registration settings.</p></div><a class="button button-primary" href="#create-event">Create an event</a></div>
    @if (session('status'))<p class="confirmation-status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="organizer-card"><p class="form-error">Please review the highlighted fields and try again.</p><ul class="form-error">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @forelse ($events as $event)
        <article class="managed-event">
            <div class="event-admin-top"><div><h2>{{ $event->title }}</h2><p>{{ $event->starts_at->format('M j, Y Â· g:i A') }} Â· {{ $event->venue }}</p></div><span class="status-pill {{ $event->status }}">{{ $event->status }}</span></div>
            <div class="management-details">
                <details><summary>Edit event details / registration status</summary>
                    <form class="organizer-form" method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">@csrf @method('PATCH')
                        <label>Event title<input name="title" value="{{ $event->title }}" required maxlength="255"></label>
                        <label>Description<textarea name="description" required>{{ $event->description }}</textarea></label>
                        <label>Venue<input name="venue" value="{{ $event->venue }}" required></label>
                        <div class="form-grid"><label>Starts at<input type="datetime-local" name="starts_at" value="{{ $event->starts_at->format('Y-m-d\TH:i') }}" required></label><label>Ends at<input type="datetime-local" name="ends_at" value="{{ $event->ends_at?->format('Y-m-d\TH:i') }}"></label></div>
                        <div class="form-grid"><label>Registration opens<input type="datetime-local" name="registration_opens_at" value="{{ $event->registration_opens_at?->format('Y-m-d\TH:i') }}"></label><label>Registration closes<input type="datetime-local" name="registration_closes_at" value="{{ $event->registration_closes_at?->format('Y-m-d\TH:i') }}"></label></div>
                        <div class="form-grid"><label>Currency<input name="currency" value="{{ $event->currency }}" required minlength="3" maxlength="3"></label><label>Event status<select name="status"><option value="draft" @selected($event->status === 'draft')>Draft</option><option value="published" @selected($event->status === 'published')>Published</option><option value="closed" @selected($event->status === 'closed')>Closed</option><option value="cancelled" @selected($event->status === 'cancelled')>Cancelled</option></select></label></div>
                        <label>Replace cover image<input type="file" name="cover_image" accept="image/*"></label><button class="button button-primary" type="submit">Save event</button>
                    </form>
                </details>
                <details><summary>Ticket types ({{ $event->ticketTypes->count() }})</summary>
                    <div><ul class="management-list">@foreach ($event->ticketTypes as $ticket)<li><span>{{ $ticket->name }} <small>Â· {{ $ticket->price }} {{ $event->currency }}</small></span><small>{{ max(0, $ticket->capacity - $ticket->sold) }} / {{ $ticket->capacity }} left</small></li>@endforeach</ul>
                    @foreach ($event->ticketTypes as $ticket)
                        <details><summary>Edit {{ $ticket->name }}</summary><form class="organizer-form" method="POST" action="{{ route('admin.events.tickets.update', [$event, $ticket]) }}">@csrf @method('PATCH')
                            <div class="form-grid"><label>Name<input name="name" value="{{ $ticket->name }}" required maxlength="120"></label><label>Price<input name="price" type="number" min="0" step="0.01" value="{{ $ticket->price }}" required></label></div>
                            <label>Description<input name="description" value="{{ $ticket->description }}" maxlength="1000"></label>
                            <div class="form-grid"><label>Capacity<input name="capacity" type="number" min="{{ $ticket->sold }}" value="{{ $ticket->capacity }}" required></label><label>Minimum per order<input name="minimum_per_order" type="number" min="1" max="10" value="{{ $ticket->minimum_per_order }}" required></label></div>
                            <label>Maximum per order<input name="maximum_per_order" type="number" min="1" max="10" value="{{ $ticket->maximum_per_order }}" required></label>
                            <input type="hidden" name="is_active" value="0"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked($ticket->is_active)> Ticket type active</label>
                            <button class="button button-quiet" type="submit">Save ticket type</button>
                        </form></details>
                    @endforeach
                    <form class="organizer-form" method="POST" action="{{ route('admin.events.tickets.store', $event) }}">@csrf
                        <div class="form-grid"><label>Name<input name="name" required maxlength="120" placeholder="General admission"></label><label>Price ({{ $event->currency }})<input name="price" type="number" min="0" step="0.01" required value="0"></label></div>
                        <label>Description<input name="description" maxlength="1000"></label>
                        <div class="form-grid"><label>Capacity<input name="capacity" type="number" min="1" required></label><label>Minimum per order<input name="minimum_per_order" type="number" min="1" max="10" value="1" required></label></div>
                        <label>Maximum per order<input name="maximum_per_order" type="number" min="1" max="10" value="10" required></label>
                        <button class="button button-quiet" type="submit">Add ticket type</button>
                    </form></div>
                </details>
                <details><summary>Add-ons ({{ $event->addOns->count() }})</summary>
                    <div><ul class="management-list">@foreach ($event->addOns as $addOn)<li><span>{{ $addOn->name }} <small>Â· {{ $addOn->price }} {{ $event->currency }}</small></span><small>{{ $addOn->capacity === null ? 'Unlimited' : max(0, $addOn->capacity - $addOn->sold) . ' left' }}</small></li>@endforeach</ul>
                    @foreach ($event->addOns as $addOn)
                        <details><summary>Edit {{ $addOn->name }}</summary><form class="organizer-form" method="POST" action="{{ route('admin.events.add-ons.update', [$event, $addOn]) }}">@csrf @method('PATCH')
                            <div class="form-grid"><label>Name<input name="name" value="{{ $addOn->name }}" required maxlength="120"></label><label>Price<input name="price" type="number" min="0" step="0.01" value="{{ $addOn->price }}" required></label></div>
                            <label>Description<input name="description" value="{{ $addOn->description }}" maxlength="1000"></label><label>Capacity (blank for unlimited)<input name="capacity" type="number" min="{{ $addOn->sold }}" value="{{ $addOn->capacity }}"></label>
                            <input type="hidden" name="is_active" value="0"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked($addOn->is_active)> Add-on active</label>
                            <button class="button button-quiet" type="submit">Save add-on</button>
                        </form></details>
                    @endforeach
                    <form class="organizer-form" method="POST" action="{{ route('admin.events.add-ons.store', $event) }}">@csrf
                        <div class="form-grid"><label>Name<input name="name" required maxlength="120" placeholder="Meal package"></label><label>Price ({{ $event->currency }})<input name="price" type="number" min="0" step="0.01" required value="0"></label></div>
                        <label>Description<input name="description" maxlength="1000"></label><label>Capacity (leave blank for unlimited)<input name="capacity" type="number" min="1"></label>
                        <button class="button button-quiet" type="submit">Add add-on</button>
                    </form></div>
                </details>
                <details><summary>Discount codes ({{ $event->discountCodes->count() }})</summary>
                    <ul class="management-list">
                        @foreach ($event->discountCodes as $discountCode)
                            <li><span><strong>{{ $discountCode->code }}</strong> <small>Â· {{ $discountCode->type === 'percentage' ? $discountCode->amount . '%' : $discountCode->amount . ' ' . $event->currency }}</small></span><small>{{ $discountCode->redemption_count }}{{ $discountCode->maximum_redemptions ? ' / ' . $discountCode->maximum_redemptions : '' }} used Â· {{ $discountCode->is_active ? 'active' : 'inactive' }}</small></li>
                            <li><form method="POST" action="{{ route('admin.events.discounts.update', [$event, $discountCode]) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $discountCode->is_active ? 0 : 1 }}"><button class="button button-quiet" type="submit">{{ $discountCode->is_active ? 'Deactivate' : 'Reactivate' }} {{ $discountCode->code }}</button></form></li>
                        @endforeach
                    </ul>
                    <form class="organizer-form" method="POST" action="{{ route('admin.events.discounts.store', $event) }}">@csrf
                        <div class="form-grid"><label>Code<input name="code" required maxlength="64" placeholder="EARLYBIRD"></label><label>Discount type<select name="type"><option value="percentage">Percentage</option><option value="fixed">Fixed amount</option></select></label></div>
                        <div class="form-grid"><label>Amount<input name="amount" type="number" min="0.01" step="0.01" required></label><label>Minimum order (optional)<input name="minimum_order" type="number" min="0" step="0.01"></label></div>
                        <div class="form-grid"><label>Max redemptions<input name="maximum_redemptions" type="number" min="1"></label><label>Expires at<input name="expires_at" type="datetime-local"></label></div>
                        <button class="button button-quiet" type="submit">Create discount</button>
                    </form>
                </details>
            </div>
        </article>
    @empty
        <div class="empty-events"><div class="empty-icon">âœ¦</div><h1>Bring people together.</h1><p>Create your first event and set up its tickets. It will appear in attendee discovery when published and registration is open.</p></div>
    @endforelse

    <section class="organizer-card" id="create-event">
        <h2>Create an event</h2>
        <form class="organizer-form" method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Event title<input name="title" value="{{ old('title') }}" required maxlength="255" placeholder="Give your event a name"></label>
            <label>Description<textarea name="description" required maxlength="10000" placeholder="Tell attendees what to expect">{{ old('description') }}</textarea></label>
            <label>Venue<input name="venue" value="{{ old('venue') }}" required maxlength="255" placeholder="Venue or online location"></label>
            <div class="form-grid"><label>Starts at<input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required></label><label>Ends at<input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"></label></div>
            <div class="form-grid"><label>Registration opens<input type="datetime-local" name="registration_opens_at" value="{{ old('registration_opens_at') }}"></label><label>Registration closes<input type="datetime-local" name="registration_closes_at" value="{{ old('registration_closes_at') }}"></label></div>
            <div class="form-grid"><label>Currency<input name="currency" value="{{ old('currency', 'PHP') }}" minlength="3" maxlength="3" required></label><label>Cover image<input type="file" name="cover_image" accept="image/*"></label></div>
            <fieldset><legend>First ticket type</legend>
                <label>Ticket name<input name="tickets[0][name]" required maxlength="120" value="{{ old('tickets.0.name', 'General admission') }}"></label>
                <label>Description<input name="tickets[0][description]" maxlength="1000" value="{{ old('tickets.0.description') }}"></label>
                <div class="form-grid"><label>Price<input name="tickets[0][price]" type="number" min="0" step="0.01" required value="{{ old('tickets.0.price', '0.00') }}"></label><label>Capacity<input name="tickets[0][capacity]" type="number" min="1" required value="{{ old('tickets.0.capacity') }}"></label></div>
                <div class="form-grid"><label>Minimum per order<input name="tickets[0][minimum_per_order]" type="number" min="1" max="10" required value="{{ old('tickets.0.minimum_per_order', 1) }}"></label><label>Maximum per order<input name="tickets[0][maximum_per_order]" type="number" min="1" max="10" required value="{{ old('tickets.0.maximum_per_order', 10) }}"></label></div>
            </fieldset>
            <label class="checkbox-label"><input type="checkbox" name="publish" value="1"> Publish now, once registration dates are ready</label>
            <button class="button button-primary" type="submit">Create event</button>
        </form>
    </section>
</div>
@endsection

