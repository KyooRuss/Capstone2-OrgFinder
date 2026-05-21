<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'System Administrator') - OrgFinder</title>
    <link rel="stylesheet" href="{{ asset('css/super-admin/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/super-admin/table.css') }}">
    <link rel="stylesheet" href="{{ asset('css/super-admin/components.css') }}">
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="{{ asset('images/AppLogo.png') }}" alt="OrgFinder Logo" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
        </div>
        <div class="brand-text">
            <div class="name">ORGFINDER</div>
            <div class="sub">Admin Panel</div>
        </div>
    </div>

    <nav class="nav">
        {{-- Organizations --}}
        <a href="{{ route('super-admin.organizations.index') }}"
           class="nav-item {{ request()->routeIs('super-admin.organizations.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
            Organizations
        </a>

        {{-- Events Dropdown --}}
        @php $eventsOpen = request()->routeIs('super-admin.events.*'); @endphp
        <div class="nav-group">
            <button class="nav-dropdown-btn {{ $eventsOpen ? 'open' : '' }}" onclick="toggleNav('eventsMenu', this)">
                <span style="display:flex;align-items:center;gap:10px;">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                    Events
                </span>
                <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="nav-submenu {{ $eventsOpen ? 'open' : '' }}" id="eventsMenu">
                @php $pending = \App\Models\Event::where('status','pending')->where('date','>=',now()->toDateString())->count(); @endphp
                <a href="{{ route('super-admin.events.upcoming') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.events.upcoming') ? 'active' : '' }}">
                    Upcoming Events
                    @if($pending > 0)
                        <span class="nav-badge">{{ $pending }}</span>
                    @endif
                </a>
                <a href="{{ route('super-admin.events.past') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.events.past') ? 'active' : '' }}">
                    Past Events
                </a>
            </div>
        </div>

        {{-- Users Dropdown --}}
        @php $usersOpen = request()->routeIs('super-admin.admin-officers.*') || request()->routeIs('super-admin.students.*'); @endphp
        <div class="nav-group">
            <button class="nav-dropdown-btn {{ $usersOpen ? 'open' : '' }}" onclick="toggleNav('usersMenu', this)">
                <span style="display:flex;align-items:center;gap:10px;">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    Users
                </span>
                <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="nav-submenu {{ $usersOpen ? 'open' : '' }}" id="usersMenu">
                <a href="{{ route('super-admin.admin-officers.index') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.admin-officers.*') ? 'active' : '' }}">
                    Admin Officers
                </a>
                <a href="{{ route('super-admin.students.index') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.students.*') ? 'active' : '' }}">
                    Students
                </a>
            </div>
        </div>

        {{-- Trash Dropdown --}}
        @php $trashOpen = request()->routeIs('super-admin.trash.*'); @endphp
        <div class="nav-group">
            <button class="nav-dropdown-btn {{ $trashOpen ? 'open' : '' }}" onclick="toggleNav('trashMenu', this)">
                <span style="display:flex;align-items:center;gap:10px;">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                    Trash
                </span>
                <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="nav-submenu {{ $trashOpen ? 'open' : '' }}" id="trashMenu">
                <a href="{{ route('super-admin.trash.organizations') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.trash.organizations') ? 'active' : '' }}">
                    Organization
                </a>
                <a href="{{ route('super-admin.trash.events') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.trash.events') ? 'active' : '' }}">
                    Events
                </a>
                <a href="{{ route('super-admin.trash.admin-officers') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.trash.admin-officers') ? 'active' : '' }}">
                    Admin Officers
                </a>
                <a href="{{ route('super-admin.trash.students') }}"
                   class="nav-item nav-sub {{ request()->routeIs('super-admin.trash.students') ? 'active' : '' }}">
                    Students
                </a>
            </div>
        </div>
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div class="user-info">
            <div class="uname">{{ auth()->user()->name }}</div>
            <div class="urole">Super Admin</div>
        </div>
        <form method="POST" action="{{ route('super-admin.logout') }}" style="margin-left:auto;">
            @csrf
            <button type="submit" class="logout-btn" title="Logout">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="rgba(255,255,255,0.6)"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            </button>
        </form>
    </div>
</aside>

{{-- Main Content --}}
<main class="main">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
        <div>
            <h1>@yield('page-title', 'Dashboard')</h1>
            <p>@yield('page-subtitle', '')</p>
        </div>
        @hasSection('header-action')
            <div>@yield('header-action')</div>
        @endif
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

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function toggleNav(menuId, btn) {
    const menu = document.getElementById(menuId);
    const isOpen = menu.classList.contains('open');
    menu.classList.toggle('open', !isOpen);
    btn.classList.toggle('open', !isOpen);
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const drop = this.closest('.filter-wrap').querySelector('.filter-drop');
        drop.classList.toggle('open');
    });
});
document.addEventListener('click', function() {
    document.querySelectorAll('.filter-drop.open').forEach(d => d.classList.remove('open'));
});
</script>

@stack('scripts')
</body>
</html>
