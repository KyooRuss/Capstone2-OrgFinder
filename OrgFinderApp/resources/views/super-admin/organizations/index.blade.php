@extends('super-admin.layouts.app')

@section('title', 'Organizations')
@section('page-title', 'Organizations Overview')
@section('page-subtitle', 'Manage and track all organizations')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Organizations: {{ $organizations->count() }}</div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8"><path d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" stroke="#94a3b8" stroke-width="2" fill="none"/></svg>
                <input type="text" name="search" placeholder="Search organizations..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-outline btn-sm">Search</button>
        </form>
        <a href="{{ route('super-admin.organizations.create') }}" class="btn btn-primary">+ Add Organization</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Organization</th>
                    <th style="text-align:center">No. of Members</th>
                    <th style="text-align:center">No. of Events</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $org)
                <tr>
                    <td>
                        <span class="td-link" onclick="loadAccess({{ $org->id }}, '{{ addslashes($org->name) }}')">
                            {{ $org->name }}
                        </span>
                    </td>
                    <td style="text-align:center">{{ $org->members_count }}</td>
                    <td style="text-align:center">{{ $org->events_count }}</td>
                    <td style="text-align:center">
                        <button class="icon-btn" title="Manage Access" onclick="loadAccess({{ $org->id }}, '{{ addslashes($org->name) }}')">
                            <svg viewBox="0 0 24 24" fill="#f59e0b"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        </button>
                        <a href="{{ route('super-admin.organizations.edit', $org) }}" class="icon-btn" title="Edit">
                            <svg viewBox="0 0 24 24" fill="#4A6CF7" width="18" height="18"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </a>
                        <button class="icon-btn" title="Delete" onclick="confirmDelete({{ $org->id }}, '{{ addslashes($org->name) }}')">
                            <svg viewBox="0 0 24 24" fill="#ef4444"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#94a3b8;padding:40px;">No organizations found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Access Modal --}}
<div class="modal-overlay" id="accessModal">
    <div class="modal" style="max-width:520px;">
        <button class="modal-close" onclick="closeModal('accessModal')">×</button>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div class="modal-title" id="accessModalTitle" style="margin-bottom:0;"></div>
            <button class="btn btn-primary btn-sm" onclick="openModal('grantModal')">+ Add User</button>
        </div>
        <p style="font-size:12px;color:#94a3b8;margin-bottom:14px;">User with access</p>
        <ul class="access-list" id="accessList"></ul>
    </div>
</div>

{{-- Grant Access Modal --}}
<div class="modal-overlay" id="grantModal">
    <div class="modal" style="max-width:420px;">
        <button class="modal-close" onclick="closeModal('grantModal')">×</button>
        <div class="modal-title" style="text-align:center;">Grant Access</div>
        <form id="grantForm">
            <div class="form-group">
                <div style="display:flex;align-items:center;gap:10px;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;background:#f8fafc;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#94a3b8"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    <input type="email" id="grantEmail" class="form-control" placeholder="Enter user email address" style="border:none;background:transparent;padding:0;" required>
                </div>
            </div>
            <div class="form-group">
                <div style="display:flex;align-items:center;gap:10px;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;background:#f8fafc;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <input type="text" id="grantName" class="form-control" placeholder="Enter user name" style="border:none;background:transparent;padding:0;">
                </div>
            </div>
            <div class="form-group">
                <div style="display:flex;align-items:center;gap:10px;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;background:#f8fafc;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65z"/></svg>
                    <input type="text" id="grantPosition" class="form-control" placeholder="Enter user position" style="border:none;background:transparent;padding:0;" required>
                </div>
            </div>
            <div id="grantError" style="color:#ef4444;font-size:12px;margin-bottom:10px;display:none;"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('grantModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>

{{-- Remove Access Confirm --}}
<div class="modal-overlay" id="removeAccessModal">
    <div class="modal" style="max-width:400px;text-align:center;">
        <div class="modal-icon">⚠️</div>
        <div style="font-size:14px;color:#374151;margin-bottom:20px;">
            Are you sure you want to remove this user from <strong id="removeOrgName"></strong>?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('removeAccessModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmRemoveAccess">Remove</button>
        </div>
    </div>
</div>

{{-- Delete Organization Confirm --}}
<div class="modal-overlay" id="deleteOrgModal">
    <div class="modal" style="max-width:400px;text-align:center;">
        <div class="modal-icon">⚠️</div>
        <div class="modal-body">
            Are you sure you want to remove this organization <strong id="deleteOrgName"></strong>?
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('deleteOrgModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteOrg">Remove</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentOrgId = null;
let currentAccessId = null;

function loadAccess(orgId, orgName) {
    currentOrgId = orgId;
    document.getElementById('accessModalTitle').textContent = orgName;
    document.getElementById('accessList').innerHTML = '<li style="color:#94a3b8;padding:10px 0;text-align:center;">Loading...</li>';
    openModal('accessModal');

    fetch(`/super-admin/organizations/${orgId}/access`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('accessList');
        if (!data.access.length) {
            list.innerHTML = '<li style="color:#94a3b8;padding:10px 0;text-align:center;">No users have access yet.</li>';
            return;
        }
        list.innerHTML = data.access.map(a => `
            <li class="access-item">
                <div class="access-avatar">${a.name.charAt(0).toUpperCase()}</div>
                <div class="access-info">
                    <div class="aname">${a.name}</div>
                    <div class="aemail">${a.email}</div>
                </div>
                <div class="access-pos">${a.position}</div>
                <button class="icon-btn" onclick="promptRemoveAccess(${a.id}, '${a.name}')">
                    <svg viewBox="0 0 24 24" fill="#ef4444" width="18" height="18"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
            </li>
        `).join('');
    });
}

function promptRemoveAccess(accessId, name) {
    currentAccessId = accessId;
    document.getElementById('removeOrgName').textContent = `"${name}"`;
    openModal('removeAccessModal');
}

document.getElementById('confirmRemoveAccess').addEventListener('click', function() {
    fetch(`/super-admin/organizations/${currentOrgId}/access/${currentAccessId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(() => {
        closeModal('removeAccessModal');
        loadAccess(currentOrgId, document.getElementById('accessModalTitle').textContent);
    });
});

document.getElementById('grantForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const errEl = document.getElementById('grantError');
    errEl.style.display = 'none';

    fetch(`/super-admin/organizations/${currentOrgId}/access`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email: document.getElementById('grantEmail').value,
            name: document.getElementById('grantName').value,
            position: document.getElementById('grantPosition').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.message && !data.errors) {
            closeModal('grantModal');
            document.getElementById('grantForm').reset();
            loadAccess(currentOrgId, document.getElementById('accessModalTitle').textContent);
        } else {
            errEl.textContent = data.message || 'Something went wrong.';
            errEl.style.display = 'block';
        }
    });
});

let deleteOrgId = null;
function confirmDelete(orgId, orgName) {
    deleteOrgId = orgId;
    document.getElementById('deleteOrgName').textContent = `"${orgName}"`;
    openModal('deleteOrgModal');
}
document.getElementById('confirmDeleteOrg').addEventListener('click', function() {
    fetch(`/super-admin/organizations/${deleteOrgId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
    })
    .then(() => location.reload());
});
</script>
@endpush
