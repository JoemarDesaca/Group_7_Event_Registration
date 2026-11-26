@extends('layouts.master')

@section('title', 'Attendance')

@section('content')
<div class="md:flex items-center justify-between my-6 page-header-breadcrumb"><div><h4 class="mb-0 text-defaulttextcolor font-medium">Attendance</h4><p class="text-textmuted">Validate issued tickets and record check-ins</p></div></div>
<div class="box"><div class="box-body">
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="flex gap-3 mb-4" aria-label="Attendance status filters">
        <a class="ti-btn {{ request('attendance') === 'present' ? 'ti-btn-primary' : 'ti-btn-light' }}" href="{{ route('admin.attendance.index', array_merge(request()->except('page'), ['attendance' => 'present'])) }}">Present</a>
        <a class="ti-btn {{ request('attendance') === 'absent' ? 'ti-btn-primary' : 'ti-btn-light' }}" href="{{ route('admin.attendance.index', array_merge(request()->except('page'), ['attendance' => 'absent'])) }}">Absent</a>
        @if (request()->filled('attendance'))<a class="ti-btn ti-btn-light" href="{{ route('admin.attendance.index', request()->except(['attendance', 'page'])) }}">All</a>@endif
    </div>
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-wrap gap-3 mb-4">
        <input class="form-control min-w-[240px]" type="search" name="search" value="{{ request('search') }}" placeholder="Ticket or registration reference, attendee, email">
        <select class="form-control" name="status"><option value="">All ticket statuses</option>@foreach (['valid', 'checked_in', 'pending_payment', 'cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select>
        <button class="ti-btn ti-btn-primary" type="submit">Search</button>
    </form>
    <div class="table-responsive"><table class="table ti-custom-table table-bordered w-full text-nowrap"><thead><tr><th>Ticket reference</th><th>Registration</th><th>Event</th><th>Attendee</th><th>Ticket</th><th>Status</th><th>Checked in</th><th>Action</th></tr></thead><tbody>
        @forelse ($tickets as $ticket)
            @php($registration = $ticket->registration)
            <tr><td><strong>{{ $ticket->reference }}</strong></td><td>{{ $registration->reference }}</td><td>{{ $registration->event->title }}</td><td>{{ $registration->attendee_name }}<small class="block">{{ $registration->attendee_email }}</small></td><td>{{ $registration->items->firstWhere('id', $ticket->registration_item_id)?->ticket_name }}</td><td><span class="badge {{ $ticket->status === 'checked_in' ? 'bg-success/10 !text-success' : ($ticket->status === 'valid' ? 'bg-primary/10 !text-primary' : 'bg-warning/10 !text-warning') }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td><td>{{ $ticket->checked_in_at?->format('M j, Y g:i A') ?? '—' }}</td><td>@if ($ticket->status === 'valid' && $registration->status === 'confirmed' && $registration->event->status !== 'cancelled')<form method="POST" action="{{ route('admin.attendance.check-in', $ticket) }}">@csrf<button class="ti-btn ti-btn-sm ti-btn-primary" type="submit">Check in</button></form>@else<span class="text-textmuted">—</span>@endif</td></tr>
        @empty
            <tr><td colspan="8" class="text-center">No issued tickets match these filters.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-4">{{ $tickets->links() }}</div>
</div></div>
@endsection
