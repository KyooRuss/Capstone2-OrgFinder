@extends('super-admin.layouts.app')

@section('title', 'Professors')
@section('page-title', 'Professors')
@section('page-subtitle', 'Manage professor accounts')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Professors: {{ count($profs) }}</div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:8px;align-items:center;">
                <div class="search-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="6"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" placeholder="Search professors..." value="{{ request('search') }}">
                </div>
                <div class="filter-wrap">
                    <button type="button" class="filter-btn">
                        {{ request('filter') ? ucfirst(request('filter')) : 'Status' }} ▼
                    </button>
                    <div class="filter-drop">
                        <a href="{{ request()->fullUrlWithQuery(['filter' => '']) }}">All</a>
                        <a href="{{ request()->fullUrlWithQuery(['filter' => 'active']) }}">Active</a>
                        <a href="{{ request()->fullUrlWithQuery(['filter' => 'blocked']) }}">Blocked</a>
                    </div>
                </div>
            </form>
            <button class="btn btn-outline" onclick="document.getElementById('createModal').style.display='flex'">
                + Add Professor
            </button>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Email Address</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profs as $prof)
                <tr>
                    <td><span style="color:#3b82f6;font-weight:600;">{{ $prof['last_name'] }}</span></td>
                    <td><span style="color:#3b82f6;font-weight:600;">{{ $prof['first_name'] }}</span></td>
                    <td>{{ $prof['email'] }}</td>
                    <td>{{ $prof['department'] }}</td>
                    <td>
                        @if($prof['status'] === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Blocked</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                            @if($prof['status'] === 'active')
                                <button class="icon-btn" title="Block" onclick="confirmBlock({{ $prof['id'] }}, '{{ addslashes($prof['first_name'] . ' ' . $prof['last_name']) }}')">
                                    <svg viewBox="0 0 24 24" fill="#94a3b8" width="20" height="20"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/></svg>
                                </button>
                            @else
                                <button class="icon-btn" title="Unblock" onclick="confirmUnblock({{ $prof['id'] }})">
                                    <svg viewBox="0 0 24 24" fill="#22c55e" width="20" height="20"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/></svg>
                                </button>
                            @endif
                            <button class="icon-btn" title="Delete" onclick="confirmDelete({{ $prof['id'] }}, '{{ addslashes($prof['first_name'] . ' ' . $prof['last_name']) }}')">
                                <svg viewBox="0 0 24 24" fill="#ef4444" width="18" height="18"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;">No professors found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Modal --}}
<div id="createModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:16px;font-weight:700;color:#1e3a5c;margin-bottom:20px;">Add Professor</h3>
        <form id="createForm">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Institutional Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Department</label>
                <input type="text" name="department" class="form-control" placeholder="e.g. College of Information Technology" required>
            </div>
            <div style="margin-bottom:20px;">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div id="createFormError" style="display:none;color:#dc2626;font-size:12px;margin-bottom:10px;padding:8px 12px;background:#fee2e2;border-radius:8px;"></div>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="document.getElementById('createModal').style.display='none'"
                    style="padding:8px 18px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;font-weight:600;color:#64748b;background:#fff;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                    style="padding:8px 20px;background:#4361EE;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

const createErrEl = document.getElementById('createFormError');

document.getElementById('createForm').addEventListener('submit', function(e) {
    e.preventDefault();
    createErrEl.style.display = 'none';
    const btn  = this.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Creating...';

    fetch('{{ route('super-admin.profs.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: new FormData(this),
    })
    .then(async r => {
        const data = await r.json();
        if (r.ok) {
            location.reload();
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.message || 'Something went wrong.');
            createErrEl.textContent = msg;
            createErrEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Create';
        }
    })
    .catch(() => {
        createErrEl.textContent = 'Network error. Please try again.';
        createErrEl.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Create';
    });
});

function confirmBlock(id, name) {
    if (!confirm(`Block ${name}?`)) return;
    fetch(`{{ url('super-admin/profs') }}/${id}/block`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(() => location.reload());
}

function confirmUnblock(id) {
    fetch(`{{ url('super-admin/profs') }}/${id}/unblock`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(() => location.reload());
}

function confirmDelete(id, name) {
    if (!confirm(`Delete ${name}? This cannot be undone.`)) return;
    fetch(`{{ url('super-admin/profs') }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(() => location.reload());
}
</script>
@endpush
