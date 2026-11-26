@extends('layouts.master')

@section('title', 'User Management')

@section('content')
<div class="grid grid-cols-12 mt-8 gap-x-6">
    <div class="col-span-12">
        <div class="box">
            <div class="box-header flex items-center justify-between">
                <div class="box-title">List of Users</div>
                <a class="ti-btn ti-btn-primary" href="{{ route('users.create') }}">Create User</a>
            </div>
            <div class="box-body">
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="table-responsive">
                    <table class="table table-auto order-table ti-custom-table table-bordered mb-0 text-nowrap w-full !border-defaultborder dark:!border-defaultborder/10">
                        <thead class="bg-light dark:bg-black/20"><tr>
                            <th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th>
                            <th scope="col">Date Registered</th><th scope="col">Date Updated</th><th scope="col">Actions</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td><td>{{ $user->email }}</td>
                                    <td><span class="badge {{ $user->isAdmin() ? 'bg-primary/10 !text-primary' : 'bg-success/10 !text-success' }}">{{ ucfirst($user->isAdmin() ? 'admin' : 'attendee') }}</span></td>
                                    <td>{{ $user->created_at?->format('d-m-Y') }}</td><td>{{ $user->updated_at?->format('d-m-Y') }}</td>
                                    <td><div class="flex gap-2">
                                        <a aria-label="Edit {{ $user->name }}" href="{{ route('users.edit', $user) }}" class="ti-btn ti-btn-sm ti-btn-primary"><i class="ri-edit-line"></i></a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?')">@csrf @method('DELETE')<button aria-label="Delete {{ $user->name }}" class="ti-btn ti-btn-sm ti-btn-danger" type="submit"><i class="ri-delete-bin-7-line"></i></button></form>
                                        @endif
                                    </div></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer">{{ $users->links() }}</div>
        </div>
    </div>
</div>
@endsection
