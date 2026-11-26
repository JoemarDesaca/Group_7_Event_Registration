<nav class="main-menu-container nav nav-pills flex-column sub-open" aria-label="Main navigation">
    <ul class="main-menu">
        <li class="slide__category"><span class="category-name">Main</span></li>
        <li class="slide"><a href="{{ route('dashboard') }}" class="side-menu__item"><span class="side-menu__label">Dashboard</span></a></li>
        @if (auth()->user()->isAdmin())
            <li class="slide"><a href="{{ route('admin.events.index') }}" class="side-menu__item"><span class="side-menu__label">Create Event</span></a></li>
            <li class="slide"><a href="{{ route('admin.ticketing.index') }}" class="side-menu__item"><span class="side-menu__label">Ticketing</span></a></li>
            <li class="slide"><a href="{{ route('admin.attendance.index') }}" class="side-menu__item"><span class="side-menu__label">Attendance</span></a></li>
            <li class="slide"><a href="{{ route('user.index') }}" class="side-menu__item"><span class="side-menu__label">User Management</span></a></li>
        @else
            <li class="slide"><a href="{{ route('events.index') }}" class="side-menu__item"><span class="side-menu__label">Events</span></a></li>
        @endif
    </ul>
</nav>
