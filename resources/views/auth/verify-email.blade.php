<x-guest-layout>
    <p class="auth-info">We sent a verification link to the email address you used to register. Open that message to finish setting up your account.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="auth-session-status" role="status">A fresh verification link has been sent to your email address.</div>
    @endif

    <div class="auth-actions-row">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="auth-submit" type="submit">Resend verification email <span aria-hidden="true">→</span></button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="auth-quiet-button" type="submit">Log out</button>
        </form>
    </div>
</x-guest-layout>
