@extends('layouts.master')

@section('title', 'Create User')

@section('content')
<div class="grid grid-cols-12 gap-6 mt-4 text-defaultsize">
    <div class="xl:col-span-12 col-span-12"><div class="box">
        <div class="box-header flex justify-between"><div class="box-title">Create User</div></div>
        <div class="box-body">
            @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('users.store') }}">@csrf
                <div class="grid grid-cols-12 sm:gap-6">
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="fullname">Full Name</label><input id="fullname" class="form-control" name="fullname" value="{{ old('fullname') }}" required>@error('fullname')<span class="text-sm text-red-500">{{ $message }}</span>@enderror</div>
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="email">Email Address</label><input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>@error('email')<span class="text-sm text-red-500">{{ $message }}</span>@enderror</div>
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="role">Role</label><select id="role" class="form-control" name="role" required><option value="attendee" @selected(old('role', 'attendee') === 'attendee')>Attendee</option><option value="admin" @selected(old('role') === 'admin')>Admin</option></select></div>
                    <div class="xl:col-span-4 md:col-span-6 col-span-12"><label class="form-label" for="password">Password</label><input id="password" type="password" class="form-control" name="password" required minlength="8">@error('password')<span class="text-sm text-red-500">{{ $message }}</span>@enderror</div>
                </div>
                <button type="submit" class="mt-4 ti-btn ti-btn-primary">Create User</button>
            </form>
        </div>
    </div></div>
</div>
@endsection
