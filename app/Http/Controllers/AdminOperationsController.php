<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\IssuedTicket;
use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminOperationsController extends Controller
{
    public function dashboard(): View
    {
        $eventCount = Event::query()->count();
        $registrationCount = Registration::query()->count();
        $ticketCount = IssuedTicket::query()->count();
        $checkedInCount = IssuedTicket::query()->where('status', 'checked_in')->count();
        $recentRegistrations = Registration::query()
            ->with(['event', 'items'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('eventCount', 'registrationCount', 'ticketCount', 'checkedInCount', 'recentRegistrations'));
    }

    public function ticketing(Request $request): View
    {
        $registrations = Registration::query()
            ->with(['event', 'items', 'tickets'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('attendee_name', 'like', "%{$search}%")
                        ->orWhere('attendee_email', 'like', "%{$search}%")
                        ->orWhereHas('event', fn ($eventQuery) => $eventQuery->where('title', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('event'), fn ($query) => $query->where('event_id', $request->integer('event')))
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $events = Event::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.ticketing', compact('registrations', 'events'));
    }

    public function attendance(Request $request): View
    {
        $tickets = IssuedTicket::query()
            ->with(['registration.event', 'registration.items'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('registration', function ($registrationQuery) use ($search): void {
                            $registrationQuery->where(function ($query) use ($search): void {
                                $query->where('reference', 'like', "%{$search}%")
                                    ->orWhere('attendee_name', 'like', "%{$search}%")
                                    ->orWhere('attendee_email', 'like', "%{$search}%")
                                    ->orWhereHas('event', fn ($eventQuery) => $eventQuery->where('title', 'like', "%{$search}%"));
                            });
                        });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->input('attendance') === 'present', fn ($query) => $query->where('status', 'checked_in'))
            ->when($request->input('attendance') === 'absent', fn ($query) => $query->where('status', 'valid'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance', compact('tickets'));
    }

    public function checkIn(IssuedTicket $ticket): RedirectResponse
    {
        DB::transaction(function () use ($ticket): void {
            $lockedTicket = IssuedTicket::query()
                ->with('registration.event')
                ->lockForUpdate()
                ->findOrFail($ticket->id);

            if ($lockedTicket->status !== 'valid'
                || $lockedTicket->registration->status !== 'confirmed'
                || $lockedTicket->registration->event->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'ticket' => 'This ticket is invalid, already checked in, unpaid, or belongs to a cancelled event.',
                ]);
            }

            $lockedTicket->forceFill(['status' => 'checked_in', 'checked_in_at' => now()])->save();
        });

        return back()->with('status', 'Ticket checked in successfully.');
    }
}
