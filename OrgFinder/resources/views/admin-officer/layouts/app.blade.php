<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Officer Panel') - OrgFinder</title>
    <link rel="stylesheet" href="{{ asset('css/admin-officer/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-officer/table.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-officer/components.css') }}">
    @stack('styles')
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="{{ asset('images/AppLogo.png') }}" alt="NEUORG Logo" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
        </div>
        <div class="brand-text">
            <div class="name">NEU ORG</div>
            <div class="sub">Admin Officer Panel</div>
        </div>
    </div>

    <nav class="nav">
        <a href="{{ route('admin-officer.organization.index') }}"
           class="nav-item {{ request()->routeIs('admin-officer.organization.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
            Organization
        </a>

        <a href="{{ route('admin-officer.events.index') }}"
           class="nav-item {{ request()->routeIs('admin-officer.events.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
            Events
        </a>

        <a href="{{ route('admin-officer.members.index') }}"
           class="nav-item {{ request()->routeIs('admin-officer.members.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Members
        </a>

        <a href="{{ route('admin-officer.recruitment.applications') }}"
           class="nav-item {{ request()->routeIs('admin-officer.recruitment.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Recruitment
        </a>

        <a href="{{ route('admin-officer.announcements.index') }}"
           class="nav-item {{ request()->routeIs('admin-officer.announcements.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 11v2h4v-2h-4zm-2 6.61c.96.71 2.21 1.65 3.2 2.39.4-.53.8-1.07 1.2-1.6-.99-.74-2.24-1.68-3.2-2.4-.4.54-.8 1.08-1.2 1.61zM20.4 5.6c-.4-.53-.8-1.07-1.2-1.6-.99.74-2.24 1.68-3.2 2.4.4.53.8 1.07 1.2 1.6.96-.72 2.21-1.65 3.2-2.4zM4 9c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2h1v4h2v-4h1l5 3V6L8 9H4zm11.5 3c0-1.33-.58-2.53-1.5-3.35v6.69c.92-.81 1.5-2.01 1.5-3.34z"/></svg>
            Announcements
        </a>

        <a href="{{ route('admin-officer.chat.index') }}"
           class="nav-item {{ request()->routeIs('admin-officer.chat.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
            Group Chat
        </a>

        @php $trashOpen = request()->routeIs('admin-officer.trash.*'); @endphp
        <div class="nav-group">
            <button class="nav-dropdown-btn {{ $trashOpen ? 'open' : '' }}" onclick="toggleNav('trashMenu', this)">
                <span style="display:flex;align-items:center;gap:10px;">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                    Trash
                </span>
                <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="nav-submenu {{ $trashOpen ? 'open' : '' }}" id="trashMenu">
                <a href="{{ route('admin-officer.trash.members') }}"
                   class="nav-item nav-sub {{ request()->routeIs('admin-officer.trash.members') ? 'active' : '' }}">
                    Members
                </a>
            </div>
        </div>
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
        <div class="user-info">
            <div class="uname">{{ auth()->user()->name ?? 'Admin' }}</div>
            <div class="urole">Admin</div>
        </div>
        <form method="POST" action="{{ route('admin-officer.logout') }}">
            @csrf
            <button type="submit" class="logout-btn" title="Logout">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="rgba(255,255,255,0.7)">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                </svg>
            </button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <h1>@yield('page-title', 'Dashboard')</h1>
        <p>@yield('page-subtitle', '')</p>
    </div>

    <div class="content">
        @if(session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</main>

<script>
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function toggleNav(menuId, btn) {
    const menu = document.getElementById(menuId);
    const isOpen = menu.classList.contains('open');
    menu.classList.toggle('open', !isOpen);
    btn.classList.toggle('open', !isOpen);
}
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
    // close filter dropdowns on outside click
    if (!e.target.closest('.filter-wrap')) {
        document.querySelectorAll('.filter-drop').forEach(d => d.classList.remove('open'));
    }
});
function toggleFilter(id) {
    const drop = document.getElementById(id);
    drop.classList.toggle('open');
}
</script>
@stack('scripts')
</body>
</html>
