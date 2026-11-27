<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Gather events | {{ request()->routeIs('register') ? 'Create account' : (request()->routeIs('password.confirm') ? 'Confirm password' : (request()->routeIs('password.*') ? 'Password help' : (request()->routeIs('verification.notice') ? 'Verify email' : 'Sign in'))) }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700,800|manrope:500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-body">
        @php
            $authPage = match (true) {
                request()->routeIs('register') => ['kicker' => 'JOIN GATHER', 'title' => 'Create your account', 'description' => 'Join in and find your next favorite event.'],
                request()->routeIs('password.request') => ['kicker' => 'ACCOUNT SUPPORT', 'title' => 'Reset your password', 'description' => 'We’ll help you get back to your account.'],
                request()->routeIs('password.reset') => ['kicker' => 'ACCOUNT SUPPORT', 'title' => 'Choose a new password', 'description' => 'Set a new password to get back to your account.'],
                request()->routeIs('password.confirm') => ['kicker' => 'SECURITY CHECK', 'title' => 'Confirm your password', 'description' => 'Confirm your identity to continue to this secure area.'],
                request()->routeIs('verification.notice') => ['kicker' => 'ONE QUICK STEP', 'title' => 'Verify your email', 'description' => 'Confirm your email address to finish setting up your account.'],
                default => ['kicker' => 'WELCOME BACK', 'title' => 'Sign in to Gather', 'description' => 'Your next great experience is just around the corner.'],
            };
        @endphp
        <div class="auth-page">
            <header class="auth-header">
                <a class="auth-brand" href="{{ url('/') }}" aria-label="Gather events home">
                    <span class="auth-brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M7 3.75v3m10-3v3M4.75 9.25h14.5M6.75 5.75h10.5a2 2 0 0 1 2 2v10.5a2 2 0 0 1-2 2H6.75a2 2 0 0 1-2-2V7.75a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="m9 14 1.8 1.8 4.2-4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span>Gather events</span>
                </a>

                <nav class="auth-nav" aria-label="Main navigation">
                    <a href="{{ url('/') }}">Home</a>
                    <a href="{{ url('/#upcoming-events') }}">Explore Events</a>
                    <a href="{{ url('/#how-it-works') }}">About</a>
                    @if (Route::has('register'))
                        <a class="auth-nav-action" href="{{ route(request()->routeIs('login') ? 'register' : 'login') }}">
                            {{ request()->routeIs('login') ? 'Get Started' : 'Sign In' }}
                        </a>
                    @endif
                </nav>
            </header>

            <main class="auth-main">
                <section class="auth-story" aria-labelledby="auth-story-title">
                    <p class="auth-eyebrow">YOUR NEXT EXPERIENCE STARTS HERE</p>
                    <h1 id="auth-story-title">Discover events.<br><span>Make memories.</span></h1>
                    <p class="auth-story-copy">Find exciting events, explore new experiences, and secure your spot with a simple and convenient registration process.</p>

                    <div class="auth-event-art" aria-hidden="true">
                        <div class="auth-art-orbit"></div>
                        <span class="auth-spark auth-spark-one"></span>
                        <span class="auth-spark auth-spark-two"></span>
                        <article class="auth-event-preview">
                            <div class="auth-preview-top">
                                <span>MAKE ROOM FOR SOMETHING NEW</span>
                                <span class="auth-preview-icon">
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M7 3.5v3m10-3v3M4.5 9h15M6.5 5.5h11a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </span>
                            </div>
                            <h2>Find your next experience</h2>
                            <p>Discover a gathering, learn something new, and make a day of it.</p>
                            <div class="auth-preview-lines"><span></span><span></span><span></span></div>
                            <div class="auth-preview-foot"><span>✓</span>A place for every occasion</div>
                        </article>
                        <div class="auth-ticket">
                            <span class="auth-ticket-mark">✦</span>
                            <span><strong>Your place awaits</strong><small>Simple event registration</small></span>
                        </div>
                    </div>
                </section>

                <section class="auth-card" aria-labelledby="auth-card-title">
                    <div class="auth-card-heading">
                        <p class="auth-card-kicker">{{ $authPage['kicker'] }}</p>
                        <h2 id="auth-card-title">{{ $authPage['title'] }}</h2>
                        <p>{{ $authPage['description'] }}</p>
                    </div>

                    {{ $slot }}

                    @if (request()->routeIs('register') && Route::has('login'))
                        <p class="auth-card-footnote">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                    @elseif (request()->routeIs('login') && Route::has('register'))
                        <p class="auth-card-footnote">New to Gather? <a href="{{ route('register') }}">Create an account</a></p>
                    @endif
                </section>
            </main>

            <footer class="auth-footer">
                <span>© {{ date('Y') }} Gather events</span>
                <span>Find your people. Make a day of it.</span>
            </footer>
        </div>
    </body>
</html>
