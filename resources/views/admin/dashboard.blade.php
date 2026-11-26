@extends('layouts.master')

@section('title', 'Admin Dashboard')

@section('content')
<div class="md:flex block items-center justify-between my-6 page-header-breadcrumb">
    <div><h4 class="mb-0 text-defaulttextcolor font-medium">Welcome back, {{ auth()->user()->name }}</h4><p class="text-textmuted">Event registration overview</p></div>
    <a href="{{ route('admin.events.index') }}" class="ti-btn ti-btn-primary">Create Event</a>
</div>
<div class="grid grid-cols-12 gap-x-6">
    <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 col-span-12"><div class="box overflow-hidden sales-box bg-primary-gradient"><div class="px-4 py-5"><p class="text-fixed-white text-xs">EVENTS</p><h3 class="text-fixed-white font-bold">{{ number_format($eventCount) }}</h3></div></div></div>
    <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 col-span-12"><div class="box overflow-hidden sales-card bg-success-gradient"><div class="px-4 py-5"><p class="text-fixed-white text-xs">REGISTRATIONS</p><h3 class="text-fixed-white font-bold">{{ number_format($registrationCount) }}</h3></div></div></div>
    <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 col-span-12"><div class="box overflow-hidden sales-card bg-warning-gradient"><div class="px-4 py-5"><p class="text-fixed-white text-xs">TICKETS ISSUED</p><h3 class="text-fixed-white font-bold">{{ number_format($ticketCount) }}</h3></div></div></div>
    <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 col-span-12"><div class="box overflow-hidden sales-card bg-danger-gradient"><div class="px-4 py-5"><p class="text-fixed-white text-xs">CHECKED IN</p><h3 class="text-fixed-white font-bold">{{ number_format($checkedInCount) }}</h3></div></div></div>
</div>
<div class="box mt-5"><div class="box-header"><div class="box-title">Recent registrations</div><a class="ms-auto text-primary" href="{{ route('admin.ticketing.index') }}">View ticketing</a></div><div class="box-body"><div class="table-responsive"><table class="table ti-custom-table table-bordered w-full"><thead><tr><th>Event</th><th>Registration reference</th><th>Attendee</th><th>Tickets</th><th>Status</th><th>Registered</th></tr></thead><tbody>
    @forelse ($recentRegistrations as $registration)
        <tr><td>{{ $registration->event->title }}</td><td>{{ $registration->reference }}</td><td>{{ $registration->attendee_name }}<small class="block">{{ $registration->attendee_email }}</small></td><td>{{ $registration->items->sum('quantity') }}</td><td>{{ ucfirst(str_replace('_', ' ', $registration->status)) }}</td><td>{{ $registration->created_at->format('M j, Y g:i A') }}</td></tr>
    @empty
        <tr><td colspan="6" class="text-center">No registrations yet. Published events with open registration will appear to attendees.</td></tr>
    @endforelse
</tbody></table></div></div></div>
@endsection
