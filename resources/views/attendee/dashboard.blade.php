@extends('layouts.master')

@section('title', 'Attendee Dashboard')

@section('content')
<div class="md:flex block items-center justify-between my-6 page-header-breadcrumb">
    <div><h4 class="mb-0 text-defaulttextcolor font-medium">Hi, {{ auth()->user()->name }}!</h4><p class="text-textmuted">Your event registration dashboard</p></div>
    <a class="ti-btn ti-btn-primary" href="{{ route('events.index') }}">Browse Events</a>
</div>
<div class="grid grid-cols-12 gap-x-6">
    <div class="xl:col-span-4 md:col-span-6 col-span-12"><div class="box"><div class="box-body"><p class="text-textmuted">Your registrations</p><h3>{{ $registrations->count() }}</h3></div></div></div>
    <div class="xl:col-span-8 col-span-12"><div class="box"><div class="box-header"><div class="box-title">Recent registrations</div></div><div class="box-body">
        @forelse ($registrations as $registration)
            <a class="flex justify-between border-b py-3" href="{{ route('registrations.show', $registration) }}"><span>{{ $registration->event->title }}<small class="block text-textmuted">{{ $registration->event->starts_at->format('M j, Y · g:i A') }}</small></span><strong>{{ ucfirst(str_replace('_', ' ', $registration->status)) }}</strong></a>
        @empty
            <p>You have no registrations yet. Browse events to find one that interests you.</p>
        @endforelse
    </div></div></div>
</div>
@endsection
