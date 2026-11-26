<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $registrations = $request->user()->registrations()->with('event')->latest()->take(5)->get();

        return view('attendee.dashboard', compact('registrations'));
    }

    public function events(Request $request): View
    {
        abort_if($request->user()->isAdmin(), 403);

        $events = Event::query()
            ->where('status', 'published')
            ->where('starts_at', '>', now())
            ->whereHas('ticketTypes', fn ($query) => $query->where('is_active', true)->whereRaw('(capacity - sold) >= minimum_per_order'))
            ->where(fn ($query) => $query->whereNull('registration_opens_at')->orWhere('registration_opens_at', '<=', now()))
            ->where(fn ($query) => $query->where('registration_closes_at', '>', now())->orWhere(fn ($query) => $query->whereNull('registration_closes_at')->where('starts_at', '>', now())))
            ->with(['ticketTypes' => fn ($query) => $query->where('is_active', true), 'addOns' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('starts_at')
            ->get();
        $selectedEvent = $request->query->has('event')
            ? $events->firstWhere('id', (int) $request->query('event'))
            : $events->first();
        abort_if($request->query->has('event') && $selectedEvent === null, 404);
        $registrations = $request->user()->registrations()->with('event')->latest()->take(5)->get();

        return view('events.index', compact('events', 'selectedEvent', 'registrations'));
    }

    public function previewDiscount(Request $request, RegistrationService $service): JsonResponse
    {
        abort_if($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'tickets' => ['nullable', 'array'], 'tickets.*' => ['integer', 'min:0', 'max:10'],
            'add_ons' => ['nullable', 'array'], 'add_ons.*' => ['integer', 'min:0', 'max:10'],
            'discount_code' => ['required', 'string', 'max:64'],
        ]);
        $event = Event::query()->findOrFail($data['event_id']);
        abort_unless($event->isAcceptingRegistrations(), 422);
        $quote = $service->quote($event, $data['tickets'] ?? [], $data['add_ons'] ?? [], $data['discount_code']);

        return response()->json([
            'discount_amount' => $this->formatCents($quote['discount_amount']),
            'total' => $this->formatCents($quote['total']),
            'currency' => $event->currency,
        ]);
    }

    public function store(StoreRegistrationRequest $request, RegistrationService $service): RedirectResponse
    {
        $data = $request->validated();
        $event = Event::query()->findOrFail($data['event_id']);
        $registration = $service->register(
            $request->user(), $event,
            array_map('intval', $data['tickets'] ?? []),
            array_map('intval', $data['add_ons'] ?? []),
            $data['discount_code'] ?? null,
            $data['attendee_name'], $data['attendee_email'],
        );

        return redirect()->route('registrations.show', $registration);
    }

    public function show(Request $request, Registration $registration): View
    {
        abort_unless($registration->user_id === $request->user()->id, 403);
        $registration->load(['event', 'items.tickets', 'addOns', 'tickets', 'discountCode']);

        return view('registrations.show', compact('registration'));
    }

    private function formatCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
