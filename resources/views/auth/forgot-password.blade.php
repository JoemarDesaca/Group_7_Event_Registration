<x-guest-layout>
    <p class="auth-info">No problem. Enter the email address on your account and we’ll send you a link to choose a new password.</p>

    <x-auth-session-status class="auth-session-status" :status="session('status')" />

    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="email">Email address</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">
            <x-input-error :messages="$errors->get('email')" class="auth-input-error" />
        </div>

        <button class="auth-submit" type="submit">Email reset link <span aria-hidden="true">→</span></button>
    </form>
</x-guest-layout>
