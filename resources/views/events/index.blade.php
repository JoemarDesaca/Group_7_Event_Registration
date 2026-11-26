@extends('layouts.master')

@section('title', 'Find an event')

@section('content')
<div class="registration-shell">
    @if ($events->isEmpty())
        <section class="empty-events">
            <div class="empty-icon">âœ¦</div>
            <p class="eyebrow">YOUR NEXT EXPERIENCE</p>
            <h1>There are no open registrations right now.</h1>
            <p>Check back soon. New events will appear here when organizers open registration.</p>
            @if ($registrations->isNotEmpty())
                <div class="past-registrations"><h2>Your registrations</h2>@foreach ($registrations as $registration)<a href="{{ route('registrations.show', $registration) }}">{{ $registration->event->title }} <span>{{ $registration->reference }}</span></a>@endforeach</div>
            @endif
        </section>
    @else
        <section class="registration-grid" data-registration-app>
            <aside class="event-panel">
                <div class="event-panel-glow"></div>
                <div class="event-brand-row"><span class="brand-mark brand-mark-light">g</span><span>Gather events</span></div>
                <div class="event-hero">
                    @if ($selectedEvent->cover_image)
                        <img class="event-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($selectedEvent->cover_image) }}" alt="Cover for {{ $selectedEvent->title }}">
                    @else
                        <div class="event-cover event-cover-placeholder" aria-hidden="true"><span>{{ mb_strtoupper(mb_substr($selectedEvent->title, 0, 1)) }}</span></div>
                    @endif
                    <p class="event-date-pill">{{ $selectedEvent->starts_at->format('D, M j Â· g:i A') }}</p>
                    <h1>{{ $selectedEvent->title }}</h1>
                    <p class="event-venue">âŒ– <span>{{ $selectedEvent->venue }}</span></p>
                    <p class="event-description">{{ $selectedEvent->description }}</p>
                </div>
                <div class="summary-card" aria-live="polite">
                    <div class="summary-heading"><h2>Order summary</h2><span class="ticket-count"><b data-ticket-count>0</b> tickets Â· <b data-addon-count>0</b> add-ons</span></div>
                    <p class="summary-empty" data-summary-empty>No tickets selected yet</p>
                    <div class="summary-lines">
                        <div><span>Tickets</span><strong data-ticket-subtotal>0.00 {{ $selectedEvent->currency }}</strong></div>
                        <div><span>Add-ons</span><strong data-addon-subtotal>0.00 {{ $selectedEvent->currency }}</strong></div>
                        <div class="discount-line" hidden><span>Discount</span><strong data-discount-total>âˆ’0.00 {{ $selectedEvent->currency }}</strong></div>
                    </div>
                    <div class="summary-total"><span>Total</span><strong><small>{{ $selectedEvent->currency }}</small> <span data-order-total>0.00</span></strong></div>
                </div>
                <p class="secure-note"><span>âœ“</span> Your details are kept private and secure.</p>
            </aside>

            <div class="checkout-panel">
                <div class="checkout-heading">
                    <div><p class="eyebrow">MAKE IT A DATE</p><h2>Choose your experience</h2><p>Select an event, then pick the tickets that work for you.</p></div>
                </div>
                <label class="field-label" for="event-picker">Available events</label>
                <select id="event-picker" class="event-picker" onchange="window.location.href='{{ route('events.index') }}?event='+this.value">
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected($event->id === $selectedEvent->id)>{{ $event->title }} Â· {{ $event->starts_at->format('M j, Y') }} Â· {{ $event->venue }}</option>
                    @endforeach
                </select>

                <form id="registration-form" method="POST" action="{{ route('registrations.store') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $selectedEvent->id }}">
                    <input type="hidden" name="discount_code" value="" data-discount-code>
                    <section class="form-section">
                        <div class="section-heading"><div><span class="step-number">01</span><h3>Select tickets</h3></div><span class="section-caption">Choose at least one</span></div>
                        @error('tickets')<p class="form-error">{{ $message }}</p>@enderror
                        <div class="ticket-list">
                            @forelse ($selectedEvent->ticketTypes as $ticket)
                                @php($remaining = max(0, $ticket->capacity - $ticket->sold))
                                @php($remaining = $remaining < $ticket->minimum_per_order ? 0 : $remaining)
                                @php($ticketPriceParts = explode('.', (string) $ticket->price))
                                <article class="choice-row {{ $remaining === 0 ? 'choice-sold-out' : '' }}" data-choice-row data-kind="ticket" data-unit-cents="{{ ((int) $ticketPriceParts[0] * 100) + (int) str_pad($ticketPriceParts[1] ?? '0', 2, '0') }}" data-name="{{ $ticket->name }}">
                                    <div class="choice-copy"><h4>{{ $ticket->name }}</h4><p>{{ $ticket->description ?: 'Admission to ' . $selectedEvent->title }}</p><span class="availability">{{ $remaining ? $remaining . ' available' : 'Sold out' }}</span></div>
                                    <div class="choice-price">{{ $ticket->price == 0 ? 'Free' : $ticket->price . ' ' . $selectedEvent->currency }}</div>
                                    <div class="quantity-control" aria-label="Quantity for {{ $ticket->name }}">
                                        <button type="button" data-quantity-change="-1" aria-label="Remove one {{ $ticket->name }}" @disabled($remaining === 0)>âˆ’</button>
                                        <input aria-label="{{ $ticket->name }} quantity" name="tickets[{{ $ticket->id }}]" value="{{ old('tickets.' . $ticket->id, 0) }}" min="0" max="{{ min($remaining, $ticket->maximum_per_order) }}" data-quantity data-minimum="{{ $ticket->minimum_per_order }}" data-maximum="{{ min($remaining, $ticket->maximum_per_order) }}" @disabled($remaining === 0)>
                                        <button type="button" data-quantity-change="1" aria-label="Add one {{ $ticket->name }}" @disabled($remaining === 0)>+</button>
                                    </div>
                                </article>
                            @empty
                                <p class="inline-empty">Tickets have not been added for this event yet.</p>
                            @endforelse
                        </div>
                    </section>

                    @if ($selectedEvent->addOns->isNotEmpty())
                        <section class="form-section add-on-section">
                            <div class="section-heading"><div><span class="step-number">02</span><h3>Make it extra special</h3></div><span class="section-caption">Optional add-ons</span></div>
                            <div class="ticket-list">
                                @foreach ($selectedEvent->addOns as $addOn)
                                    @php($remaining = $addOn->capacity === null ? 10 : max(0, $addOn->capacity - $addOn->sold))
                                    @php($addOnPriceParts = explode('.', (string) $addOn->price))
                                    <article class="choice-row {{ $remaining === 0 ? 'choice-sold-out' : '' }}" data-choice-row data-kind="addon" data-unit-cents="{{ ((int) $addOnPriceParts[0] * 100) + (int) str_pad($addOnPriceParts[1] ?? '0', 2, '0') }}" data-name="{{ $addOn->name }}">
                                        <div class="choice-copy"><h4>{{ $addOn->name }}</h4><p>{{ $addOn->description ?: 'Optional event add-on' }}</p><span class="availability">{{ $remaining ? ($addOn->capacity === null ? 'Available' : $remaining . ' available') : 'Sold out' }}</span></div>
                                        <div class="choice-price">{{ $addOn->price == 0 ? 'Free' : $addOn->price . ' ' . $selectedEvent->currency }}</div>
                                        <div class="quantity-control" aria-label="Quantity for {{ $addOn->name }}"><button type="button" data-quantity-change="-1" @disabled($remaining === 0)>âˆ’</button><input aria-label="{{ $addOn->name }} quantity" name="add_ons[{{ $addOn->id }}]" value="{{ old('add_ons.' . $addOn->id, 0) }}" min="0" max="{{ min($remaining, 10) }}" data-quantity @disabled($remaining === 0)><button type="button" data-quantity-change="1" @disabled($remaining === 0)>+</button></div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="form-section discount-section">
                        <details>
                            <summary><span>ï¼‹</span> Have a discount code?</summary>
                            <div class="discount-entry"><input type="text" maxlength="64" placeholder="Enter code" aria-label="Discount code" value="{{ old('discount_code') }}" data-discount-input><button type="button" class="button button-quiet" data-apply-discount>Apply code</button></div>
                            <p class="discount-feedback" role="status" data-discount-feedback></p>
                        </details>
                    </section>

                    <section class="form-section attendee-section">
                        <div class="section-heading"><div><span class="step-number">03</span><h3>Attendee details</h3></div><span class="section-caption">Confirmation goes here</span></div>
                        <div class="attendee-fields">
                            <label class="field-label">Full name<input name="attendee_name" value="{{ old('attendee_name', auth()->user()->name) }}" required maxlength="255" autocomplete="name">@error('attendee_name')<span class="form-error">{{ $message }}</span>@enderror</label>
                            <label class="field-label">Email address<input type="email" name="attendee_email" value="{{ old('attendee_email', auth()->user()->email) }}" required maxlength="255" autocomplete="email">@error('attendee_email')<span class="form-error">{{ $message }}</span>@enderror</label>
                        </div>
                        @error('event_id')<p class="form-error">{{ $message }}</p>@enderror
                        @error('discount_code')<p class="form-error">{{ $message }}</p>@enderror
                        <button class="button button-primary register-button" type="submit">Complete registration <span aria-hidden="true">â†’</span></button>
                        <p class="payment-note">Paid registrations will be saved as pending payment until a payment option is connected.</p>
                    </section>
                </form>
                @if ($registrations->isNotEmpty())
                    <section class="recent-orders"><p class="eyebrow">YOUR ACTIVITY</p><h3>Recent registrations</h3>@foreach ($registrations as $registration)<a href="{{ route('registrations.show', $registration) }}"><span>{{ $registration->event->title }}</span><strong>{{ $registration->status === 'pending_payment' ? 'Payment pending' : $registration->reference }}</strong></a>@endforeach</section>
                @endif
            </div>
        </section>
    @endif
</div>

@if ($events->isNotEmpty())
<script>
(() => {
    const form = document.querySelector('#registration-form');
    const currency = @json($selectedEvent->currency);
    const cents = (value) => { const [whole, fraction = ''] = String(value).split('.'); return Number(whole) * 100 + Number((fraction + '00').slice(0, 2)); };
    const money = (value) => `${Math.floor(value / 100)}.${String(value % 100).padStart(2, '0')}`;
    const elements = {
        ticketCount: document.querySelector('[data-ticket-count]'), addonCount: document.querySelector('[data-addon-count]'), ticketSubtotal: document.querySelector('[data-ticket-subtotal]'),
        addonSubtotal: document.querySelector('[data-addon-subtotal]'), total: document.querySelector('[data-order-total]'),
        discountLine: document.querySelector('.discount-line'), discountTotal: document.querySelector('[data-discount-total]'),
        empty: document.querySelector('[data-summary-empty]'),
    };
    let appliedDiscount = 0;
    const updateSummary = () => {
        let tickets = 0; let addons = 0; let ticketTotal = 0; let addonTotal = 0;
        form.querySelectorAll('[data-choice-row]').forEach((row) => {
            const input = row.querySelector('[data-quantity]');
            const quantity = Math.max(0, Math.min(Number(input.max), Number(input.value) || 0));
            input.value = String(quantity);
            const line = quantity * Number(row.dataset.unitCents);
            if (row.dataset.kind === 'ticket') { tickets += quantity; ticketTotal += line; } else { addons += quantity; addonTotal += line; }
        });
        elements.ticketCount.textContent = String(tickets);
        elements.addonCount.textContent = String(addons);
        elements.ticketSubtotal.textContent = `${money(ticketTotal)} ${currency}`;
        elements.addonSubtotal.textContent = `${money(addonTotal)} ${currency}`;
        elements.empty.hidden = tickets > 0;
        elements.discountLine.hidden = appliedDiscount <= 0;
        elements.discountTotal.textContent = `âˆ’${money(appliedDiscount)} ${currency}`;
        elements.total.textContent = money(Math.max(0, ticketTotal + addonTotal - appliedDiscount));
        form.dataset.selectedTickets = String(tickets);
    };

    form.addEventListener('click', (event) => {
        const button = event.target.closest('[data-quantity-change]');
        if (!button) return;
        const row = button.closest('[data-choice-row]');
        const input = row.querySelector('[data-quantity]');
        const next = Math.max(0, Math.min(Number(input.max), Number(input.value) + Number(button.dataset.quantityChange)));
        if (row.dataset.kind === 'ticket' && next > 0) input.value = String(Math.max(next, Number(input.dataset.minimum)));
        else input.value = String(next);
        appliedDiscount = 0;
        form.querySelector('[data-discount-code]').value = '';
        updateSummary();
    });
    form.addEventListener('input', (event) => {
        if (event.target.matches('[data-quantity]')) { appliedDiscount = 0; form.querySelector('[data-discount-code]').value = ''; updateSummary(); }
    });
    document.querySelector('[data-apply-discount]').addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const feedback = document.querySelector('[data-discount-feedback]');
        const code = document.querySelector('[data-discount-input]').value.trim();
        if (!code) { feedback.textContent = 'Enter a code to apply it.'; return; }
        button.disabled = true; feedback.textContent = 'Checking codeâ€¦';
        const body = new FormData(form); body.set('discount_code', code);
        try {
            const response = await fetch(@json(route('registrations.discount-preview')), {
                method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'}, body,
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.errors?.discount_code?.[0] || 'This code cannot be applied to the current order.');
            appliedDiscount = cents(result.discount_amount);
            form.querySelector('[data-discount-code]').value = code;
            feedback.textContent = `Code applied. You save ${result.discount_amount} ${result.currency}.`;
            updateSummary();
        } catch (error) { appliedDiscount = 0; form.querySelector('[data-discount-code]').value = ''; feedback.textContent = error.message; updateSummary(); }
        finally { button.disabled = false; }
    });
    form.addEventListener('submit', (event) => {
        if (Number(form.dataset.selectedTickets) < 1) { event.preventDefault(); document.querySelector('.ticket-list').scrollIntoView({behavior: 'smooth', block: 'center'}); }
    });
    updateSummary();
})();
</script>
@endif
@endsection

