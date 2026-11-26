<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::query()->latest()->paginate(15);

        return view('user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fullname' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'attendee'])],
        ]);

        User::query()->create([
            'name' => $data['fullname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('user.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'attendee'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        abort_if($user->isAdmin() && $data['role'] === 'attendee' && User::query()->where('role', 'admin')->count() <= 1, 422, 'At least one admin account must remain.');
        $user->name = $data['fullname'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('user.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->id === $user->id, 422, 'You cannot remove your own account.');
        abort_if($user->isAdmin() && User::query()->where('role', 'admin')->count() <= 1, 422, 'At least one admin account must remain.');
        abort_if($user->events()->exists() || $user->registrations()->exists(), 422, 'This user has event or registration records and cannot be deleted.');
        $user->delete();

        return redirect()->route('user.index')->with('success', 'User deleted successfully.');
    }
}
