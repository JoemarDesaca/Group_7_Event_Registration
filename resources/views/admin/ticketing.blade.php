@extends('layouts.master')

@section('title', 'Ticketing')

@section('content')
<div class="md:flex items-center justify-between my-6 page-header-breadcrumb"><div><h4 class="mb-0 text-defaulttextcolor font-medium">Ticketing</h4><p class="text-textmuted">Registrations and issued ticket details</p></div></div>
<div class="box"><div class="box-body">
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="GET" action="{{ route('admin.ticketing.index') }}" class="grid grid-cols-12 gap-3 mb-4">
        <div class="col-span-12 md:col-span-5"><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search registration, attendee, email, or event"></div>
        <div class="col-span-12 md:col-span-3"><select class="form-control" name="status"><option value="">All statuses</option>@foreach (['confirmed', 'pending_payment', 'cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
        <div class="col-span-12 md:col-span-3"><select class="form-control" name="event"><option value="">All events</option>@foreach ($events as $event)<option value="{{ $event->id }}" @selected((string) request('event') === (string) $event->id)>{{ $event->title }}</option>@endforeach</select></div>
        <div class="col-span-12 md:col-span-1"><button class="ti-btn ti-btn-primary" type="submit">Filter</button></div>
    </form>
    <div class="table-responsive"><table class="table ti-custom-table table-bordered w-full text-nowrap"><thead><tr><th>Event</th><th>Registration</th><th>Attendee</th><th>Ticket type / quantity</th><th>Status</th><th>Registered</th></tr></thead><tbody>
        @forelse ($registrations as $registration)
            <tr><td>{{ $registration->event->title }}</td><td><strong>{{ $registration->reference }}</strong>@foreach ($registration->tickets as $ticket)<small class="block">{{ $ticket->reference }}</small>@endforeach</td><td>{{ $registration->attendee_name }}<small class="block">{{ $registration->attendee_email }}</small></td><td>@foreach ($registration->items as $item)<div>{{ $item->ticket_name }} × {{ $item->quantity }}</div>@endforeach</td><td><span class="badge bg-primary/10 !text-primary">{{ ucfirst(str_replace('_', ' ', $registration->status)) }}</span></td><td>{{ $registration->created_at->format('M j, Y g:i A') }}</td></tr>
        @empty
            <tr><td colspan="6" class="text-center">No registrations match these filters.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-4">{{ $registrations->links() }}</div>
</div></div>
@endsection
