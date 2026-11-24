<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Event Registration') · {{ config('app.name', 'Gather') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|manrope:500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="registration-app">
    <header class="site-header">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">H</span><span>Hussers</span></a>
        <div class="header-user">
            <span>Signed in as <strong>{{ auth()->user()->name }}</strong></span>
            <a href="{{ route('profile.edit') }}">Account</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
        </div>
    </header>
    <main>@yield('content')</main>
</body>
</html>
