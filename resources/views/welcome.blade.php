<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <title>Gather events</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root { color-scheme: light; font-family: 'DM Sans', 'Figtree', Inter, ui-sans-serif, system-ui, sans-serif; color: #172554; background: #fff; font-synthesis: none; text-rendering: optimizeLegibility; }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { min-width: 320px; margin: 0; color: #172554; background: #fff; }
        a { color: inherit; }
        .welcome-page { overflow: hidden; }
        .wrap { width: min(1160px, calc(100% - 48px)); margin-inline: auto; }
        .topbar { position: relative; z-index: 5; background: rgba(255,255,255,.96); border-bottom: 1px solid #e8eef8; }
        .nav-row { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 28px; }
        .wordmark { display: inline-flex; align-items: center; gap: 11px; text-decoration: none; font-size: 18px; font-weight: 800; letter-spacing: -.55px; color: #172554; white-space: nowrap; }
        .brand-icon { display: grid; width: 38px; height: 38px; place-items: center; border-radius: 12px; color: white; background: linear-gradient(145deg, #3b82f6, #164e9a); box-shadow: 0 7px 16px #2563eb2b; }
        .brand-icon svg { width: 21px; height: 21px; }
        .nav-links { display: flex; align-items: center; gap: 30px; margin-left: auto; }
        .nav-links a, .nav-actions a { color: #52627d; font-size: 14px; font-weight: 600; text-decoration: none; transition: color .18s ease, background .18s ease, transform .18s ease; }
        .nav-links a:hover, .nav-actions .sign-in:hover { color: #2563eb; }
        .nav-actions { display: flex; align-items: center; gap: 22px; }
        .nav-actions .sign-in { color: #164e9a; }
        .button { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 0 21px; border: 1px solid transparent; border-radius: 10px; font-size: 14px; font-weight: 700; text-decoration: none; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
        .button:hover { transform: translateY(-2px); }
        .button-primary { color: #fff !important; background: #2563eb; box-shadow: 0 8px 18px #2563eb2a; }
        .button-primary:hover { background: #164e9a; box-shadow: 0 10px 22px #164e9a35; }
        .button-outline { border-color: #cbd8ec; color: #164e9a; background: #fff; }
        .button-outline:hover { border-color: #93b4eb; background: #f5f9ff; }
        .mobile-menu { display: none; }
        .hero { position: relative; isolation: isolate; padding: 76px 0 82px; background: linear-gradient(180deg, #f7faff 0%, #fff 100%); }
        .hero::before { position: absolute; z-index: -1; top: -220px; right: -170px; width: 600px; height: 600px; border: 1px solid #dbeafe; border-radius: 50%; box-shadow: 0 0 0 42px #eff6ff80, 0 0 0 100px #eff6ff45; content: ''; }
        .hero-grid { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 44px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; margin: 0 0 20px; color: #2563eb; font-size: 11px; font-weight: 800; letter-spacing: 1.55px; }
        .eyebrow::before { width: 24px; height: 2px; border-radius: 2px; background: #60a5fa; content: ''; }
        h1, h2, h3, p { margin-top: 0; }
        .hero h1 { max-width: 560px; margin-bottom: 20px; color: #12244d; font-size: clamp(44px, 5.3vw, 66px); font-weight: 800; letter-spacing: -3.2px; line-height: 1.06; }
        .hero h1 span { color: #2563eb; }
        .hero-copy { max-width: 510px; margin-bottom: 30px; color: #64748b; font-size: 17px; line-height: 1.75; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .hero-art { position: relative; min-height: 390px; display: grid; place-items: center; }
        .art-orbit { position: absolute; width: 350px; height: 350px; border: 1px solid #bfdbfe; border-radius: 50%; background: radial-gradient(circle at 50% 48%, #eff6ff 0, #eff6ff99 49%, transparent 70%); }
        .art-orbit::before, .art-orbit::after { position: absolute; border: 1px dashed #bfdbfe; border-radius: 50%; content: ''; }
        .art-orbit::before { inset: 28px; }
        .art-orbit::after { inset: 64px; }
        .visual-card { position: relative; z-index: 1; width: min(330px, 82%); padding: 25px; border: 1px solid #e5edf9; border-radius: 22px; background: #fff; box-shadow: 0 28px 70px #1e3a8a20; transform: rotate(-3deg); }
        .visual-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 21px; }
        .visual-card-label { color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .visual-card-mark { display: grid; width: 38px; height: 38px; place-items: center; border-radius: 12px; color: #2563eb; background: #eff6ff; }
        .visual-card-mark svg { width: 20px; height: 20px; }
        .visual-card h2 { margin-bottom: 8px; color: #172554; font-size: 23px; letter-spacing: -.7px; }
        .visual-card p { margin-bottom: 19px; color: #64748b; font-size: 13px; line-height: 1.6; }
        .visual-lines { display: grid; gap: 9px; }
        .visual-line { height: 9px; border-radius: 99px; background: #eff4fb; }
        .visual-line:nth-child(1) { width: 88%; }
        .visual-line:nth-child(2) { width: 68%; }
        .visual-line:nth-child(3) { width: 78%; }
        .visual-card-bottom { display: flex; align-items: center; gap: 9px; margin-top: 22px; padding-top: 17px; border-top: 1px solid #edf2f8; color: #2563eb; font-size: 12px; font-weight: 700; }
        .visual-card-bottom span { display: grid; width: 25px; height: 25px; place-items: center; border-radius: 8px; color: #fff; background: #2563eb; }
        .floating-ticket { position: absolute; z-index: 2; right: 3%; bottom: 35px; width: 150px; display: flex; align-items: center; gap: 11px; padding: 14px 15px; border: 1px solid #e3ecf8; border-radius: 15px; background: white; box-shadow: 0 14px 35px #1e3a8a1c; transform: rotate(5deg); }
        .floating-ticket-icon { display: grid; width: 37px; height: 37px; flex: 0 0 auto; place-items: center; border-radius: 11px; color: #164e9a; background: #dbeafe; }
        .floating-ticket-icon svg { width: 19px; height: 19px; }
        .floating-ticket strong, .floating-ticket small { display: block; }
        .floating-ticket strong { color: #172554; font-size: 11px; }
        .floating-ticket small { margin-top: 4px; color: #64748b; font-size: 9px; }
        .spark { position: absolute; z-index: 1; width: 12px; height: 12px; border-radius: 4px; background: #60a5fa; transform: rotate(45deg); }
        .spark-one { top: 22%; left: 7%; }
        .spark-two { right: 9%; top: 20%; width: 8px; height: 8px; background: #93c5fd; }
        .section { padding: 86px 0; }
        .section-heading { max-width: 650px; margin: 0 auto 42px; text-align: center; }
        .section-heading .eyebrow { margin-bottom: 14px; }
        .section-heading .eyebrow::after { width: 24px; height: 2px; border-radius: 2px; background: #60a5fa; content: ''; }
        .section-heading h2 { margin-bottom: 12px; color: #172554; font-size: clamp(30px, 4vw, 40px); letter-spacing: -1.5px; line-height: 1.16; }
        .section-heading p { margin-bottom: 0; color: #64748b; font-size: 15px; line-height: 1.7; }
        .features { background: #fff; }
        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .feature-card { min-height: 230px; padding: 27px; border: 1px solid #e5edf9; border-radius: 17px; background: linear-gradient(145deg, #f8fbff, #fff 72%); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .feature-card:hover { transform: translateY(-4px); border-color: #c5dcff; box-shadow: 0 18px 38px #1e3a8a0d; }
        .feature-icon { display: grid; width: 48px; height: 48px; margin-bottom: 20px; place-items: center; border-radius: 14px; color: #2563eb; background: #eaf3ff; }
        .feature-icon svg { width: 23px; height: 23px; }
        .feature-card h3 { margin-bottom: 9px; color: #172554; font-size: 17px; letter-spacing: -.3px; }
        .feature-card p { margin-bottom: 0; color: #64748b; font-size: 14px; line-height: 1.7; }
        .events-section { background: #f7faff; }
        .events-section .section-heading { margin-bottom: 32px; }
        .empty-state { max-width: 720px; margin-inline: auto; padding: 46px 28px; border: 1px solid #e0eaf8; border-radius: 20px; background: #fff; text-align: center; box-shadow: 0 16px 45px #1e3a8a0a; }
        .empty-icon { display: grid; width: 58px; height: 58px; margin: 0 auto 18px; place-items: center; border-radius: 18px; color: #2563eb; background: #eff6ff; }
        .empty-icon svg { width: 27px; height: 27px; }
        .empty-state h3 { margin-bottom: 9px; color: #172554; font-size: 19px; }
        .empty-state p { max-width: 430px; margin: 0 auto 20px; color: #64748b; font-size: 14px; line-height: 1.7; }
        .text-link { display: inline-flex; align-items: center; gap: 7px; color: #2563eb; font-size: 13px; font-weight: 700; text-decoration: none; }
        .text-link:hover { color: #164e9a; }
        .steps { background: #fff; }
        .steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .step { position: relative; padding: 10px 20px 10px 0; }
        .step-number { display: grid; width: 46px; height: 46px; margin-bottom: 20px; place-items: center; border: 1px solid #bfdbfe; border-radius: 14px; color: #2563eb; background: #eff6ff; font-size: 16px; font-weight: 800; }
        .step h3 { margin-bottom: 8px; color: #172554; font-size: 17px; }
        .step p { max-width: 300px; margin-bottom: 0; color: #64748b; font-size: 14px; line-height: 1.7; }
        .cta-section { padding: 0 0 86px; }
        .cta-panel { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 28px; padding: 43px 50px; border-radius: 22px; color: white; background: linear-gradient(115deg, #164e9a, #2563eb 70%, #3b82f6); }
        .cta-panel::after { position: absolute; top: -150px; right: 130px; width: 340px; height: 340px; border: 1px solid #ffffff26; border-radius: 50%; box-shadow: 0 0 0 35px #ffffff0d, 0 0 0 75px #ffffff0a; content: ''; }
        .cta-copy { position: relative; z-index: 1; }
        .cta-copy h2 { max-width: 570px; margin-bottom: 10px; font-size: clamp(25px, 3.5vw, 34px); letter-spacing: -1px; line-height: 1.2; }
        .cta-copy p { max-width: 550px; margin-bottom: 0; color: #dbeafe; font-size: 14px; line-height: 1.65; }
        .cta-panel .button { position: relative; z-index: 1; flex: 0 0 auto; color: #164e9a; background: #fff; box-shadow: 0 8px 20px #102f6a35; }
        .cta-panel .button:hover { background: #eff6ff; }
        .footer { border-top: 1px solid #e8eef8; background: #fff; }
        .footer-main { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 44px; padding: 43px 0 35px; }
        .footer-brand p { max-width: 300px; margin: 15px 0 0; color: #64748b; font-size: 13px; line-height: 1.7; }
        .footer h3 { margin: 5px 0 14px; color: #172554; font-size: 13px; }
        .footer-links { display: grid; justify-items: start; gap: 10px; }
        .footer-links a { color: #64748b; font-size: 13px; text-decoration: none; }
        .footer-links a:hover { color: #2563eb; }
        .footer-bottom { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 17px 0; border-top: 1px solid #edf2f8; color: #8492a8; font-size: 12px; }
        :focus-visible { outline: 3px solid #60a5fa; outline-offset: 3px; }
        @media (max-width: 900px) {
            .nav-links { gap: 19px; }
            .nav-actions { gap: 14px; }
            .hero { padding: 62px 0 67px; }
            .hero-grid { gap: 20px; }
            .hero-art { min-height: 340px; }
            .art-orbit { width: 310px; height: 310px; }
            .floating-ticket { right: -2%; }
        }
        @media (max-width: 700px) {
            .wrap { width: min(100% - 36px, 540px); }
            .nav-row { min-height: 68px; flex-wrap: wrap; gap: 0; }
            .wordmark { font-size: 16px; }
            .brand-icon { width: 35px; height: 35px; }
            .mobile-menu { display: block; }
            .mobile-menu summary { display: grid; width: 42px; height: 42px; place-items: center; border: 1px solid #dbe6f5; border-radius: 11px; color: #164e9a; list-style: none; cursor: pointer; }
            .mobile-menu summary::-webkit-details-marker { display: none; }
            .mobile-menu summary svg { width: 20px; height: 20px; }
            .mobile-menu[open] summary { background: #eff6ff; }
            .nav-content { display: none; flex-basis: 100%; padding: 10px 0 17px; }
            .mobile-menu[open] .nav-content { display: block; }
            .desktop-links, .desktop-actions { display: none; }
            .nav-links { display: grid; gap: 0; margin: 0; }
            .nav-links a { padding: 12px 4px; border-bottom: 1px solid #edf2f8; }
            .nav-actions { gap: 14px; padding-top: 14px; }
            .nav-actions .button { min-height: 42px; padding-inline: 15px; }
            .hero { padding: 53px 0 48px; }
            .hero-grid { grid-template-columns: 1fr; gap: 12px; }
            .hero-copy-block { text-align: center; }
            .hero .eyebrow { justify-content: center; font-size: 10px; }
            .hero h1 { margin-inline: auto; font-size: clamp(42px, 12vw, 56px); letter-spacing: -2.5px; }
            .hero-copy { margin-inline: auto; font-size: 15px; }
            .hero-actions { justify-content: center; }
            .hero-art { min-height: 320px; margin-top: 8px; }
            .art-orbit { width: 290px; height: 290px; }
            .visual-card { width: min(300px, 78%); padding: 21px; }
            .floating-ticket { right: 1%; bottom: 24px; }
            .section { padding: 66px 0; }
            .section-heading { margin-bottom: 30px; }
            .section-heading h2 { font-size: 32px; }
            .feature-grid, .steps-grid { grid-template-columns: 1fr; gap: 14px; }
            .feature-card { min-height: auto; padding: 23px; }
            .feature-icon { margin-bottom: 16px; }
            .empty-state { padding: 36px 20px; }
            .steps-grid { gap: 26px; }
            .step { display: grid; grid-template-columns: 46px 1fr; column-gap: 15px; padding: 0; }
            .step-number { grid-row: 1 / span 2; margin: 0; }
            .step h3 { margin: 3px 0 6px; }
            .step p { grid-column: 2; }
            .cta-section { padding-bottom: 65px; }
            .cta-panel { align-items: flex-start; flex-direction: column; padding: 32px 25px; border-radius: 18px; }
            .cta-panel::after { right: -100px; }
            .footer-main { grid-template-columns: 1fr 1fr; gap: 30px 20px; padding-top: 34px; }
            .footer-brand { grid-column: 1 / -1; }
            .footer-bottom { align-items: flex-start; flex-direction: column; gap: 5px; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $brandName = 'Gather events';
    $homeRoute = route('dashboard');
    $exploreRoute = auth()->check()
        ? ($isAdmin ? route('dashboard') : route('events.index'))
        : (Route::has('events.index') ? route('events.index') : route('login'));
    $getStartedRoute = auth()->check()
        ? route('dashboard')
        : (Route::has('register') ? route('register') : route('login'));
@endphp
<div class="welcome-page">
    <header class="topbar">
        <nav class="wrap nav-row" aria-label="Main navigation">
            <a class="wordmark" href="{{ url('/') }}" aria-label="{{ $brandName }} home">
                <span class="brand-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3m10-3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 14 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span>{{ $brandName }}</span>
            </a>
            <details class="mobile-menu">
                <summary aria-label="Toggle navigation"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></summary>
                <div class="nav-content">
                    <div class="nav-links">
                        <a href="{{ url('/') }}">Home</a>
                        <a href="{{ $exploreRoute }}">Explore Events</a>
                        <a href="#how-it-works">About</a>
                    </div>
                    <div class="nav-actions">
                        @auth
                            <a class="sign-in" href="{{ $homeRoute }}">Dashboard</a>
                        @else
                            @if (Route::has('login'))<a class="sign-in" href="{{ route('login') }}">Sign In</a>@endif
                            <a class="button button-primary" href="{{ $getStartedRoute }}">Get Started</a>
                        @endauth
                    </div>
                </div>
            </details>
            <div class="nav-links desktop-links">
                <a href="{{ url('/') }}">Home</a>
                <a href="{{ $exploreRoute }}">Explore Events</a>
                <a href="#how-it-works">About</a>
            </div>
            <div class="nav-actions desktop-actions">
                @auth
                    <a class="sign-in" href="{{ $homeRoute }}">Dashboard</a>
                @else
                    @if (Route::has('login'))<a class="sign-in" href="{{ route('login') }}">Sign In</a>@endif
                    <a class="button button-primary" href="{{ $getStartedRoute }}">Get Started</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="wrap hero-grid">
                <div class="hero-copy-block">
                    <p class="eyebrow">YOUR NEXT EXPERIENCE STARTS HERE</p>
                    <h1>Discover Events.<br><span>Make Memories.</span></h1>
                    <p class="hero-copy">Find exciting events, explore new experiences, and secure your spot with a simple and convenient registration process.</p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="{{ $exploreRoute }}">Explore Events <span aria-hidden="true">→</span></a>
                        <a class="button button-outline" href="{{ $getStartedRoute }}">{{ auth()->check() ? 'Go to Dashboard' : 'Get Started' }}</a>
                    </div>
                </div>
                <div class="hero-art" aria-label="Illustration of event discovery and registration" role="img">
                    <div class="art-orbit" aria-hidden="true"></div>
                    <span class="spark spark-one" aria-hidden="true"></span><span class="spark spark-two" aria-hidden="true"></span>
                    <div class="visual-card" aria-hidden="true">
                        <div class="visual-card-top"><span class="visual-card-label">Make room for something new</span><span class="visual-card-mark"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3m10-3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div>
                        <h2>Find your next experience</h2>
                        <p>Discover a gathering, learn something new, and make a day of it.</p>
                        <div class="visual-lines"><span class="visual-line"></span><span class="visual-line"></span><span class="visual-line"></span></div>
                        <div class="visual-card-bottom"><span>✓</span> A place for every occasion</div>
                    </div>
                    <div class="floating-ticket" aria-hidden="true"><span class="floating-ticket-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 8a2 2 0 0 0 0 4 2 2 0 0 1 0 4v2h16v-2a2 2 0 0 1 0-4 2 2 0 0 0 0-4V6H4v2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M13 7v2m0 2v2m0 2v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><span><strong>Your place awaits</strong><small>Simple event registration</small></span></div>
                </div>
            </div>
        </section>

        <section class="section features" id="features">
            <div class="wrap">
                <div class="section-heading"><p class="eyebrow">MADE FOR GOOD EXPERIENCES</p><h2>A simpler way to make plans</h2><p>From the first search to the day of the event, find what you need in one convenient place.</p></div>
                <div class="feature-grid">
                    <article class="feature-card"><div class="feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3m10-3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 13h.01M12 13h.01M16 13h.01M8 16h.01M12 16h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></div><h3>Discover Events</h3><p>Explore a variety of events and find experiences that match your interests.</p></article>
                    <article class="feature-card"><div class="feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 8a2 2 0 0 0 0 4 2 2 0 0 1 0 4v2h16v-2a2 2 0 0 1 0-4 2 2 0 0 0 0-4V6H4v2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M13 7v2m0 2v2m0 2v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div><h3>Easy Registration</h3><p>Choose your preferred event, select your tickets, and complete your registration in a few steps.</p></article>
                    <article class="feature-card"><div class="feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/></svg></div><h3>Stay Organized</h3><p>Keep your registrations together and return to your event information whenever you need it.</p></article>
                </div>
            </div>
        </section>

        <section class="section events-section" id="upcoming-events">
            <div class="wrap">
                <div class="section-heading"><p class="eyebrow">FIND SOMETHING TO LOOK FORWARD TO</p><h2>Find Your Next Event</h2><p>Discover upcoming experiences and secure your place.</p></div>
                <div class="empty-state"><div class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3m10-3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div><h3>No events available at the moment.</h3><p>Check back soon for new experiences and upcoming events.</p><a class="text-link" href="{{ $exploreRoute }}">{{ auth()->check() && $isAdmin ? 'Go to your dashboard' : 'Explore event registration' }} <span aria-hidden="true">→</span></a></div>
            </div>
        </section>

        <section class="section steps" id="how-it-works">
            <div class="wrap">
                <div class="section-heading"><p class="eyebrow">YOUR PLANS, MADE SIMPLE</p><h2>Getting Started Is Easy</h2><p>A few simple steps can take you from curious to ready.</p></div>
                <div class="steps-grid">
                    <article class="step"><span class="step-number">01</span><h3>Discover</h3><p>Browse available events and choose the experience you want.</p></article>
                    <article class="step"><span class="step-number">02</span><h3>Register</h3><p>Select your preferred ticket and complete your registration.</p></article>
                    <article class="step"><span class="step-number">03</span><h3>Attend</h3><p>Keep your registration details and get ready for your event.</p></article>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="wrap"><div class="cta-panel"><div class="cta-copy"><h2>Your Next Great Experience Is Waiting.</h2><p>Start exploring events and find something worth looking forward to.</p></div><a class="button" href="{{ $exploreRoute }}">Explore Events <span aria-hidden="true">→</span></a></div></div>
        </section>
    </main>

    <footer class="footer">
        <div class="wrap footer-main">
            <div class="footer-brand"><a class="wordmark" href="{{ url('/') }}"><span class="brand-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3m10-3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 14 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span>{{ $brandName }}</span></a><p>Making event discovery and registration simple, convenient, and accessible.</p></div>
            <div><h3>Explore</h3><div class="footer-links"><a href="{{ url('/') }}">Home</a><a href="{{ $exploreRoute }}">Explore Events</a><a href="#how-it-works">How it works</a></div></div>
            <div><h3>Your account</h3><div class="footer-links">@auth<a href="{{ $homeRoute }}">Dashboard</a>@else @if (Route::has('login'))<a href="{{ route('login') }}">Sign In</a>@endif @if (Route::has('register'))<a href="{{ route('register') }}">Get Started</a>@endif @endauth</div></div>
        </div>
        <div class="wrap footer-bottom"><span>© {{ now()->year }} {{ $brandName }}. All rights reserved.</span><span>Discover something memorable.</span></div>
    </footer>
</div>
</body>
</html>
