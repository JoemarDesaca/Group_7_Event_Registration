@extends('layouts.registration')

@section('title', 'Registration confirmation')

@section('content')
<div class="confirmation-page">
    <section class="confirmation-card">
        @if ($registration->status === 'confirmed')<div class="confirmation-mark">✓</div><p class="eyebrow">YOU'RE ON THE LIST</p><h1>Registration confirmed</h1><p>We’ve saved your registration for <strong>{{ $registration->event->title }}</strong>. Keep this reference for check-in.</p>
        @else<div class="confirmation-mark" style="color:#97651a;background:#fff3dc">…</div><p class="eyebrow">REGISTRATION SAVED</p><h1>Payment is pending</h1><p>Your place is recorded for <strong>{{ $registration->event->title }}</strong>, but payment is not connected yet. This registration is not marked as paid.</p>@endif
        <div class="reference-box">Registration reference<strong>{{ $registration->reference }}</strong></div>
        <p><strong>{{ $registration->event->starts_at->format('l, F j, Y · g:i A') }}</strong><br>{{ $registration->event->venue }}</p>
        <ul class="confirmation-lines">
            @foreach ($registration->items as $item)
                <li><span>{{ $item->ticket_name }} × {{ $item->quantity }}@foreach ($item->tickets as $ticket)<span class="ticket-reference">Ticket {{ $ticket->reference }} · {{ str_replace('_', ' ', $ticket->status) }}</span>@endforeach</span><strong>{{ $item->line_total }} {{ $registration->event->currency }}</strong></li>
            @endforeach
            @foreach ($registration->addOns as $addOn)<li><span>{{ $addOn->add_on_name }} × {{ $addOn->quantity }}</span><strong>{{ $addOn->line_total }} {{ $registration->event->currency }}</strong></li>@endforeach
        </ul>
        <div class="confirmation-total"><span>Total</span><span>{{ $registration->total }} {{ $registration->event->currency }}</span></div>
        @if ($registration->discount_amount !== '0.00')<p>Discount {{ $registration->discountCode?->code ? '('.$registration->discountCode->code.')' : '' }} applied: {{ $registration->discount_amount }} {{ $registration->event->currency }}</p>@endif
        <span class="confirmation-status {{ $registration->status }}">{{ str_replace('_', ' ', $registration->status) }}</span>
        <div class="confirmation-actions"><a class="button button-primary" href="{{ route('dashboard') }}">Explore more events</a></div>
    </section>
</div>
@endsection
