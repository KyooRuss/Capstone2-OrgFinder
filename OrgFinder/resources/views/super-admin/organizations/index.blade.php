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
                            {{ $org->org_name }}
                        </span>
                    </td>
                    <td style="text-align:center">{{ $org->members_count }}</td>
                    <td style="text-align:center">{{ $org->events_count }}</td>
                    <td style="text-align:center">
                        <button class="icon-btn" title="Manage Access" onclick="loadAccess({{ $org->id }}, '{{ addslashes($org->org_name) }}')">
                            <svg viewBox="0 0 24 24" fill="#f59e0b"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        </button>
                        <a href="{{ route('super-admin.organizations.edit', $org) }}" class="icon-btn" title="Edit">
                            <svg viewBox="0 0 24 24" fill="#4A6CF7" width="18" height="18"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </a>
                        <button class="icon-btn" title="Delete" onclick="confirmDelete({{ $org->id }}, '{{ addslashes($org->org_name) }}')">
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
            <button class="btn btn-primary btn-sm" onclick="resetGrantModal(); openModal('grantModal')">+ Add User</button>
        </div>
        <p style="font-size:12px;color:#94a3b8;margin-bottom:14px;">User with access</p>
        <ul class="access-list" id="accessList"></ul>
    </div>
</div>

{{-- Grant Access Modal (Two-Step) --}}
<div class="modal-overlay" id="grantModal">
    <div class="modal" style="max-width:460px;">
        <button class="modal-close" onclick="closeGrantModal()">×</button>

        {{-- Step 1: Search Student --}}
        <div id="grantStep1">
            <div class="modal-title" style="text-align:center;margin-bottom:4px;">Select Student</div>
            <p style="text-align:center;font-size:12px;color:#94a3b8;margin-bottom:16px;">Search by name, email, or student number</p>
            <div style="position:relative;">
                <div style="display:flex;align-items:center;gap:10px;border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 14px;background:#f8fafc;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="studentSearchInput" placeholder="Search student..." style="border:none;background:transparent;padding:0;outline:none;width:100%;font-size:14px;" oninput="searchStudents(this.value)">
                </div>
                <div id="studentSearchResults" style="margin-top:8px;max-height:260px;overflow-y:auto;display:none;border:1px solid #e2e8f0;border-radius:8px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.08);"></div>
            </div>
            <p id="grantStep1Error" style="color:#ef4444;font-size:12px;margin-top:10px;display:none;"></p>
        </div>

        {{-- Step 2: Select Position --}}
        <div id="grantStep2" style="display:none;">
            <div class="modal-title" style="text-align:center;margin-bottom:16px;">Assign Position</div>

            {{-- Selected Student Card --}}
            <div id="selectedStudentCard" style="display:flex;align-items:center;gap:14px;background:#f0f4ff;border:1.5px solid #c7d4ff;border-radius:10px;padding:14px;margin-bottom:20px;">
                <div id="selectedAvatar" style="width:44px;height:44px;border-radius:50%;background:#1a2e78;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;color:#fff;flex-shrink:0;"></div>
                <div>
                    <div id="selectedName" style="font-weight:700;font-size:15px;color:#1e3a5c;"></div>
                    <div id="selectedEmail" style="font-size:12px;color:#64748b;"></div>
                    <div id="selectedStudentNo" style="font-size:12px;color:#94a3b8;"></div>
                </div>
            </div>

            {{-- Position Dropdown --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px;">Position</label>
                <select id="grantPosition" style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 14px;font-size:14px;color:#374151;background:#f8fafc;outline:none;">
                    <option value="">Select a position</option>
                    <option>Organization Adviser</option>
                    <option>Organization President</option>
                </select>
            </div>

            <div id="grantStep2Error" style="color:#ef4444;font-size:12px;margin-bottom:10px;display:none;"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="backToStep1()">← Back</button>
                <button type="button" class="btn btn-primary" onclick="submitGrantAccess()">Grant Access</button>
            </div>
        </div>
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
    .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.json();
    })
    .then(data => {
        const list = document.getElementById('accessList');
        if (!data.access || !data.access.length) {
            list.innerHTML = '<li style="color:#94a3b8;padding:10px 0;text-align:center;">No users have access yet.</li>';
            return;
        }
        list.innerHTML = data.access.map(a => `
            <li class="access-item">
                <div class="access-avatar">${a.name ? a.name.charAt(0).toUpperCase() : '?'}</div>
                <div class="access-info">
                    <div class="aname">${a.name || ''}</div>
                    <div class="aemail">${a.email || ''}</div>
                </div>
                <div class="access-pos">${a.position || ''}</div>
                <button class="icon-btn" onclick="promptRemoveAccess(${a.id}, '${(a.name || '').replace(/'/g, "\\'")}')">
                    <svg viewBox="0 0 24 24" fill="#ef4444" width="18" height="18"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
            </li>
        `).join('');
    })
    .catch(() => {
        document.getElementById('accessList').innerHTML = '<li style="color:#ef4444;padding:10px 0;text-align:center;">Failed to load access list.</li>';
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

let selectedStudent = null;
let searchTimeout = null;

function closeGrantModal() {
    closeModal('grantModal');
    resetGrantModal();
}

function resetGrantModal() {
    selectedStudent = null;
    document.getElementById('studentSearchInput').value = '';
    document.getElementById('studentSearchResults').style.display = 'none';
    document.getElementById('studentSearchResults').innerHTML = '';
    document.getElementById('grantPosition').value = '';
    document.getElementById('grantStep1Error').style.display = 'none';
    document.getElementById('grantStep2Error').style.display = 'none';
    document.getElementById('grantStep1').style.display = 'block';
    document.getElementById('grantStep2').style.display = 'none';
}

function backToStep1() {
    selectedStudent = null;
    document.getElementById('grantStep2').style.display = 'none';
    document.getElementById('grantStep1').style.display = 'block';
    document.getElementById('grantStep2Error').style.display = 'none';
}

function searchStudents(q) {
    clearTimeout(searchTimeout);
    const resultsEl = document.getElementById('studentSearchResults');
    if (!q.trim()) { resultsEl.style.display = 'none'; return; }

    searchTimeout = setTimeout(() => {
        fetch(`/super-admin/students/search?q=${encodeURIComponent(q)}&org_id=${currentOrgId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(students => {
            if (!students.length) {
                resultsEl.innerHTML = '<div style="padding:16px;text-align:center;color:#94a3b8;font-size:13px;">No students found.</div>';
            } else {
                resultsEl.innerHTML = students.map(s => `
                    <div onclick="selectStudent(${JSON.stringify(s).replace(/"/g, '&quot;')})"
                         style="display:flex;align-items:center;gap:12px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;transition:background .15s;"
                         onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background='#fff'">
                        <div style="width:36px;height:36px;border-radius:50%;background:#1a2e78;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;color:#fff;flex-shrink:0;">
                            ${s.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:13px;color:#1e3a5c;">${s.name}</div>
                            <div style="font-size:11px;color:#64748b;">${s.email}</div>
                            <div style="font-size:11px;color:#94a3b8;">Year ${s.year_level}</div>
                        </div>
                    </div>
                `).join('');
            }
            resultsEl.style.display = 'block';
        });
    }, 300);
}

function selectStudent(student) {
    selectedStudent = student;
    document.getElementById('selectedAvatar').textContent = student.name.charAt(0).toUpperCase();
    document.getElementById('selectedName').textContent = student.name;
    document.getElementById('selectedEmail').textContent = student.email;
    document.getElementById('selectedStudentNo').textContent = 'Year ' + student.year_level;
    document.getElementById('grantStep1').style.display = 'none';
    document.getElementById('grantStep2').style.display = 'block';
}

function submitGrantAccess() {
    const position = document.getElementById('grantPosition').value;
    const errEl = document.getElementById('grantStep2Error');
    if (!position) { errEl.textContent = 'Please select a position.'; errEl.style.display = 'block'; return; }
    errEl.style.display = 'none';

    fetch(`/super-admin/organizations/${currentOrgId}/access`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: selectedStudent.email, position })
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok) {
            closeGrantModal();
            loadAccess(currentOrgId, document.getElementById('accessModalTitle').textContent);
        } else {
            errEl.textContent = data.message || 'Something went wrong.';
            errEl.style.display = 'block';
        }
    })
    .catch(() => {
        errEl.textContent = 'Request failed. Please try again.';
        errEl.style.display = 'block';
    });
}

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
