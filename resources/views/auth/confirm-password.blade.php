<x-guest-layout>
    <p class="auth-info">For your security, please enter your password before continuing.</p>

    <form class="auth-form" method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="password">Password</label>
            <input id="password" class="auth-input" type="password" name="password" required autofocus autocomplete="current-password" placeholder="Enter your password">
            <x-input-error :messages="$errors->get('password')" class="auth-input-error" />
        </div>

        <button class="auth-submit" type="submit">Confirm password <span aria-hidden="true">→</span></button>
    </form>
</x-guest-layout>
