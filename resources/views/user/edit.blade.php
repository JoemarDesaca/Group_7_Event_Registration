@extends('layouts.master')

@section('title', 'Edit User')

@section('content')
<div class="grid grid-cols-12 gap-6 mt-4 text-defaultsize">
    <div class="xl:col-span-12 col-span-12"><div class="box">
        <div class="box-header"><div class="box-title">Edit User</div></div>
        <div class="box-body">
            @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PATCH')
                <div class="grid grid-cols-12 sm:gap-6">
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="fullname">Full Name</label><input id="fullname" class="form-control" name="fullname" value="{{ old('fullname', $user->name) }}" required>@error('fullname')<span class="text-sm text-red-500">{{ $message }}</span>@enderror</div>
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="email">Email Address</label><input id="email" type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>@error('email')<span class="text-sm text-red-500">{{ $message }}</span>@enderror</div>
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="role">Role</label><select id="role" class="form-control" name="role" required><option value="attendee" @selected(old('role', $user->isAdmin() ? 'admin' : 'attendee') === 'attendee')>Attendee</option><option value="admin" @selected(old('role', $user->isAdmin() ? 'admin' : 'attendee') === 'admin')>Admin</option></select></div>
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="password">New Password <span>(leave blank to keep current)</span></label><input id="password" type="password" class="form-control" name="password" minlength="8">@error('password')<span class="text-sm text-red-500">{{ $message }}</span>@enderror</div>
                </div>
                <button type="submit" class="mt-4 ti-btn ti-btn-primary">Save Changes</button>
                <a href="{{ route('user.index') }}" class="ti-btn ti-btn-light">Cancel</a>
            </form>
        </div>
    </div></div>
</div>
@endsection
