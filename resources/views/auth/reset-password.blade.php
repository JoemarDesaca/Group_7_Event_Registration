<x-guest-layout>
    <form class="auth-form" method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-field">
            <label class="auth-label" for="email">Email address</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="you@example.com">
            <x-input-error :messages="$errors->get('email')" class="auth-input-error" />
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">New password</label>
            <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" placeholder="Choose a new password">
            <x-input-error :messages="$errors->get('password')" class="auth-input-error" />
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Enter it again">
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-input-error" />
        </div>

        <button class="auth-submit" type="submit">Reset password <span aria-hidden="true">→</span></button>
    </form>
</x-guest-layout>
