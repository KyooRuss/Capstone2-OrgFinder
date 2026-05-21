@extends('super-admin.layouts.app')

@section('title', 'Add New Organization')
@section('page-title', 'Add New Organization')
@section('page-subtitle', 'Fill in the organization details below')

@section('header-action')
    <button type="submit" form="createForm" class="btn btn-primary" style="background:#fff;color:#2F4FB5;">Create Organization</button>
@endsection

@push('styles')
<style>
    .create-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
    .section-title { font-size:13px;font-weight:700;color:#1e3a5c;text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px; }
    .inline-fields { display:flex; gap:10px; margin-bottom:12px; }
    .inline-fields .form-control { flex:1; }
    @media(max-width:768px){ .create-grid{grid-template-columns:1fr;} }

    /* Reminder banner */
    .reminder-banner {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-left: 4px solid #f59e0b;
        border-radius: 10px;
        padding: 14px 18px;
        margin-top: 20px;
    }
    .reminder-icon {
        font-size: 20px;
        line-height: 1;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .reminder-title {
        font-size: 13px;
        font-weight: 700;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 3px;
    }
    .reminder-text {
        font-size: 13px;
        color: #b45309;
    }

    /* Dynamic photo slots */
    .photo-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }

    /* Add button row */
    .add-row {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }
    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px dashed #93c5fd;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
    }
    .btn-add:hover { background: #dbeafe; border-color: #3b82f6; }

    /* Removable item wrapper */
    .removable-item {
        position: relative;
        margin-bottom: 10px;
    }
    .remove-btn {
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #ef4444;
        font-size: 16px;
        line-height: 1;
        padding: 2px 4px;
        border-radius: 4px;
        display: none;
    }
    .removable-item:hover .remove-btn { display: block; }
    .removable-item .form-control { padding-right: 30px; }

    /* Photo slot with remove */
    .photo-slot-wrap { position: relative; }
    .photo-slot-wrap .slot-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(239,68,68,0.85);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 13px;
        line-height: 1;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    .photo-slot-wrap:hover .slot-remove { display: flex; }

    /* Testimonial textarea wrapper */
    .testimonial-wrap {
        position: relative;
        margin-bottom: 10px;
    }
    .testimonial-wrap .remove-btn {
        top: 10px;
        transform: none;
    }
    .testimonial-wrap textarea { padding-right: 28px; }
</style>
@endpush

@section('content')
@if($errors->any())
<div class="flash flash-error">
    <ul style="list-style:none;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('super-admin.organizations.store') }}" enctype="multipart/form-data" id="createForm">
    @csrf
    <div class="create-grid">
        {{-- LEFT COLUMN --}}
        <div>
            <div class="card" style="margin-bottom:20px;">
                <div style="display:flex;gap:20px;align-items:flex-start;">
                    {{-- Logo --}}
                    <div>
                        <label class="logo-upload" id="logoLabel" title="Upload logo">
                            <input type="file" name="logo" accept="image/*" onchange="previewLogo(this)">
                            <img id="logoPreview" src="" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            <div id="logoPlaceholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                            </div>
                        </label>
                    </div>

                    <div style="flex:1;">
                        <input type="text" name="name" class="form-control" placeholder="Enter the organization name" value="{{ old('name') }}" required style="margin-bottom:10px;">
                        <textarea name="vision" class="form-control" placeholder="Enter the organization's vision" rows="2">{{ old('vision') }}</textarea>
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <textarea name="mission" class="form-control" placeholder="Enter the organization's mission" rows="3">{{ old('mission') }}</textarea>
                </div>
                <div class="inline-fields" style="margin-top:12px;">
                    <input type="text" name="room_number" class="form-control" placeholder="Org's room number" value="{{ old('room_number') }}">
                    <input type="text" name="contact_telegram" class="form-control" placeholder="Contact person (Telegram)" value="{{ old('contact_telegram') }}">
                    <input type="text" name="contact_facebook" class="form-control" placeholder="Contact person (Facebook)" value="{{ old('contact_facebook') }}">
                </div>
            </div>

            {{-- Photos --}}
            <div class="card">
                <p class="section-title" style="margin-bottom:12px;">Upload photos from previous events or activities</p>
                <div class="photo-grid" id="photoGrid">
                    <div class="photo-slot-wrap">
                        <label class="photo-slot" id="photoSlot0">
                            <input type="file" name="photos[]" accept="image/*" onchange="previewPhoto(this, 0)">
                            <div class="placeholder" id="photoPlaceholder0">
                                <svg viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                            </div>
                            <img id="photoPreview0" src="" style="display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                        </label>
                        <button type="button" class="slot-remove" onclick="removePhotoSlot(this)">×</button>
                    </div>
                    <div class="photo-slot-wrap">
                        <label class="photo-slot" id="photoSlot1">
                            <input type="file" name="photos[]" accept="image/*" onchange="previewPhoto(this, 1)">
                            <div class="placeholder" id="photoPlaceholder1">
                                <svg viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                            </div>
                            <img id="photoPreview1" src="" style="display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                        </label>
                        <button type="button" class="slot-remove" onclick="removePhotoSlot(this)">×</button>
                    </div>
                    <div class="photo-slot-wrap">
                        <label class="photo-slot" id="photoSlot2">
                            <input type="file" name="photos[]" accept="image/*" onchange="previewPhoto(this, 2)">
                            <div class="placeholder" id="photoPlaceholder2">
                                <svg viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                            </div>
                            <img id="photoPreview2" src="" style="display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                        </label>
                        <button type="button" class="slot-remove" onclick="removePhotoSlot(this)">×</button>
                    </div>
                </div>
                <div class="add-row">
                    <button type="button" class="btn-add" onclick="addPhotoSlot()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Add Photo
                    </button>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div>
            <div class="card" style="margin-bottom:20px;">
                <p class="section-title">Reasons to Join</p>
                <div id="reasonsList">
                    @for($i = 0; $i < 3; $i++)
                    <div class="removable-item">
                        <input type="text" name="reasons[]" class="form-control" placeholder="Add a reason to join {{ $i+1 }}" value="{{ old('reasons.'.$i) }}">
                        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
                    </div>
                    @endfor
                </div>
                <div class="add-row">
                    <button type="button" class="btn-add" onclick="addReason()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Add Reason
                    </button>
                </div>
            </div>

            <div class="card">
                <p class="section-title">Member / Alumni Testimonials</p>
                <div id="testimonialsList">
                    @for($i = 0; $i < 3; $i++)
                    <div class="testimonial-wrap">
                        <textarea name="testimonials[]" class="form-control" placeholder="Enter either member or alumni testimonial {{ $i+1 }}" rows="3">{{ old('testimonials.'.$i) }}</textarea>
                        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
                    </div>
                    @endfor
                </div>
                <div class="add-row">
                    <button type="button" class="btn-add" onclick="addTestimonial()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Add Testimonial
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reminder banner --}}
    <div class="reminder-banner">
        <div class="reminder-icon">⚠️</div>
        <div>
            <div class="reminder-title">Upcoming Events Details</div>
            <div class="reminder-text">Events can be added by organization officers after the organization is created.</div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let photoCount = 3;
let reasonCount = 3;
let testimonialCount = 3;

/* ── Logo preview ── */
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
            document.getElementById('logoPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/* ── Photo slots ── */
function previewPhoto(input, index) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById(`photoPreview${index}`);
            const placeholder = document.getElementById(`photoPlaceholder${index}`);
            if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addPhotoSlot() {
    const idx = photoCount++;
    const grid = document.getElementById('photoGrid');
    const wrap = document.createElement('div');
    wrap.className = 'photo-slot-wrap';
    wrap.innerHTML = `
        <label class="photo-slot" id="photoSlot${idx}">
            <input type="file" name="photos[]" accept="image/*" onchange="previewPhoto(this, ${idx})">
            <div class="placeholder" id="photoPlaceholder${idx}">
                <svg viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
            </div>
            <img id="photoPreview${idx}" src="" style="display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
        </label>
        <button type="button" class="slot-remove" onclick="removePhotoSlot(this)">×</button>
    `;
    grid.appendChild(wrap);
}

function removePhotoSlot(btn) {
    btn.closest('.photo-slot-wrap').remove();
}

/* ── Reasons ── */
function addReason() {
    const idx = ++reasonCount;
    const list = document.getElementById('reasonsList');
    const div = document.createElement('div');
    div.className = 'removable-item';
    div.innerHTML = `
        <input type="text" name="reasons[]" class="form-control" placeholder="Add a reason to join ${idx}">
        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
    `;
    list.appendChild(div);
}

/* ── Testimonials ── */
function addTestimonial() {
    const idx = ++testimonialCount;
    const list = document.getElementById('testimonialsList');
    const div = document.createElement('div');
    div.className = 'testimonial-wrap';
    div.innerHTML = `
        <textarea name="testimonials[]" class="form-control" placeholder="Enter either member or alumni testimonial ${idx}" rows="3"></textarea>
        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
    `;
    list.appendChild(div);
}

/* ── Generic remove ── */
function removeItem(btn) {
    btn.closest('.removable-item, .testimonial-wrap').remove();
}
</script>
@endpush
