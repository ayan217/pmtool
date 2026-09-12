<nav class="sidebar-nav">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid"></i> Dashboard
    </a>
    <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}">
        <i class="bi bi-check2-square"></i> Tasks
    </a>
    <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
        <i class="bi bi-folder2"></i> Projects
    </a>
    <a href="{{ route('developers.index') }}" class="{{ request()->routeIs('developers.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Developers
    </a>
    <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}">
        <i class="bi bi-calendar3"></i> Calendar
    </a>
    <a href="{{ route('archive.index') }}" class="{{ request()->routeIs('archive.*') ? 'active' : '' }}">
        <i class="bi bi-archive"></i> Archive
    </a>
    <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <i class="bi bi-gear"></i> Settings
    </a>
    <form method="POST" action="{{ route('logout') }}" class="logout-form">
        @csrf
        <button type="submit">
            <i class="bi bi-box-arrow-left"></i> Logout
        </button>
    </form>
</nav>
