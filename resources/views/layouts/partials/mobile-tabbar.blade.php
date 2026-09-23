<nav class="mobile-tabbar d-lg-none" aria-label="Primary">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}">
        <i class="bi bi-check2-square"></i>
        <span>Tasks</span>
    </a>
    <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}">
        <i class="bi bi-calendar3"></i>
        <span>Calendar</span>
    </a>
    <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
        <i class="bi bi-folder2"></i>
        <span>Projects</span>
    </a>
    <button type="button" data-bs-toggle="offcanvas" data-bs-target="#appOffcanvas" aria-controls="appOffcanvas">
        <i class="bi bi-list"></i>
        <span>More</span>
    </button>
</nav>
