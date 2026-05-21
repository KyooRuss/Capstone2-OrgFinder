@extends('super-admin.layouts.app')

@section('title', 'Edit Organization')
@section('page-title', 'Edit Organization')
@section('page-subtitle', 'Update the organization details below')

@section('header-action')
    <button type="submit" form="editForm" class="btn btn-primary" style="background:#fff;color:#2F4FB5;">Save Changes</button>
@endsection

@push('styles')
<style>
    .create-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
    .section-title { font-size:13px;font-weight:700;color:#1e3a5c;text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px; }
    .inline-fields { display:flex; gap:10px; margin-bottom:12px; }
    .inline-fields .form-control { flex:1; }
    @media(max-width:768px){ .create-grid{grid-template-columns:1fr;} }

    .add-row { display:flex; justify-content:flex-end; margin-top:10px; }
    .btn-add {
        display:inline-flex; align-items:center; gap:5px;
        padding:6px 14px; background:#eff6ff; color:#2563eb;
        border:1px dashed #93c5fd; border-radius:8px;
        font-size:12.5px; font-weight:600; cursor:pointer; transition:all .15s;
    }
    .btn-add:hover { background:#dbeafe; border-color:#3b82f6; }

    .removable-item { position:relative; margin-bottom:10px; }
    .remove-btn {
        position:absolute; top:50%; right:8px; transform:translateY(-50%);
        background:none; border:none; cursor:pointer; color:#ef4444;
        font-size:16px; line-height:1; padding:2px 4px; border-radius:4px; display:none;
    }
    .removable-item:hover .remove-btn { display:block; }
    .removable-item .form-control { padding-right:30px; }

    .photo-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
    .photo-slot-wrap { position:relative; }
    .photo-slot-wrap .slot-remove {
        position:absolute; top:4px; right:4px;
        background:rgba(239,68,68,0.85); color:#fff; border:none;
        border-radius:50%; width:20px; height:20px; font-size:13px;
        line-height:1; cursor:pointer; display:none;
        align-items:center; justify-content:center; z-index:10;
    }
    .photo-slot-wrap:hover .slot-remove { display:flex; }

    .testimonial-wrap { position:relative; margin-bottom:10px; }
    .testimonial-wrap .remove-btn { top:10px; transform:none; }
    .testimonial-wrap textarea { padding-right:28px; }

    .existing-photo {
        aspect-ratio:1; border-radius:10px; overflow:hidden; position:relative;
        background:#f1f5f9;
    }
    .existing-photo img { width:100%; height:100%; object-fit:cover; }
    .existing-photo .slot-remove {
        position:absolute; top:4px; right:4px;
        background:rgba(239,68,68,0.85); color:#fff; border:none;
        border-radius:50%; width:20px; height:20px; font-size:13px;
        cursor:pointer; display:none; align-items:center; justify-content:center; z-index:10;
    }
    .existing-photo:hover .slot-remove { display:flex; }
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

<form method="POST" action="{{ route('super-admin.organizations.update', $organization) }}"
      enctype="multipart/form-data" id="editForm">
    @csrf
    @method('PUT')

    <div class="create-grid">
        {{-- LEFT COLUMN --}}
        <div>
            <div class="card" style="margin-bottom:20px;">
                <div style="display:flex;gap:20px;align-items:flex-start;">
                    {{-- Logo --}}
                    <div>
                        <label class="logo-upload" id="logoLabel" title="Upload logo">
                            <input type="file" name="logo" accept="image/*" onchange="previewLogo(this)">
                            @if($organization->logo)
                                <img id="logoPreview"
                                     src="{{ asset('storage/'.$organization->logo) }}"
                                     style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                <div id="logoPlaceholder" style="display:none;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                                </div>
                            @else
                                <img id="logoPreview" src="" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                <div id="logoPlaceholder">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                                </div>
                            @endif
                        </label>
                    </div>

                    <div style="flex:1;">
                        <input type="text" name="name" class="form-control"
                               placeholder="Enter the organization name"
                               value="{{ old('name', $organization->name) }}"
                               required style="margin-bottom:10px;">
                        <textarea name="vision" class="form-control"
                                  placeholder="Enter the organization's vision" rows="2">{{ old('vision', $organization->vision) }}</textarea>
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <textarea name="mission" class="form-control"
                              placeholder="Enter the organization's mission" rows="3">{{ old('mission', $organization->mission) }}</textarea>
                </div>
                <div class="inline-fields" style="margin-top:12px;">
                    <input type="text" name="room_number" class="form-control"
                           placeholder="Org's room number"
                           value="{{ old('room_number', $organization->room_number) }}">
                    <input type="text" name="contact_telegram" class="form-control"
                           placeholder="Contact person (Telegram)"
                           value="{{ old('contact_telegram', $organization->contact_telegram) }}">
                    <input type="text" name="contact_facebook" class="form-control"
                           placeholder="Contact person (Facebook)"
                           value="{{ old('contact_facebook', $organization->contact_facebook) }}">
                </div>
            </div>

            {{-- Photos --}}
            <div class="card">
                <p class="section-title">Photos</p>
                <div class="photo-grid" id="photoGrid">
                    @foreach($organization->photos as $photo)
                    <div class="existing-photo">
                        <img src="{{ asset('storage/'.$photo->photo_path) }}" alt="">
                        <button type="button" class="slot-remove"
                                onclick="deletePhoto({{ $photo->id }}, this)">×</button>
                    </div>
                    @endforeach
                </div>
                <div class="add-row" style="margin-top:10px;">
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
                    @forelse($organization->reasons as $reason)
                    <div class="removable-item">
                        <input type="text" name="reasons[]" class="form-control"
                               value="{{ old('reasons.'.$loop->index, $reason->reason) }}">
                        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
                    </div>
                    @empty
                    <div class="removable-item">
                        <input type="text" name="reasons[]" class="form-control" placeholder="Add a reason to join">
                        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
                    </div>
                    @endforelse
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
                    @forelse($organization->testimonials as $testimonial)
                    <div class="testimonial-wrap">
                        <textarea name="testimonials[]" class="form-control" rows="3">{{ old('testimonials.'.$loop->index, $testimonial->testimonial) }}</textarea>
                        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
                    </div>
                    @empty
                    <div class="testimonial-wrap">
                        <textarea name="testimonials[]" class="form-control" rows="3" placeholder="Enter a testimonial"></textarea>
                        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>
                    </div>
                    @endforelse
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

    {{-- Hidden fields for deleted photos --}}
    <div id="deletePhotoInputs"></div>
</form>
@endsection

@push('scripts')
<script>
let photoCount = 0;

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('logoPreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('logoPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addPhotoSlot() {
    const idx = `new_${photoCount++}`;
    const grid = document.getElementById('photoGrid');
    const wrap = document.createElement('div');
    wrap.className = 'photo-slot-wrap';
    wrap.innerHTML = `
        <label class="photo-slot" style="aspect-ratio:1;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#f8fafc;overflow:hidden;position:relative;">
            <input type="file" name="photos[]" accept="image/*" onchange="previewNewPhoto(this)" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
            <div style="color:#cbd5e1;text-align:center;">
                <svg viewBox="0 0 24 24" fill="#cbd5e1" width="32" height="32"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
            </div>
        </label>
        <button type="button" class="slot-remove" onclick="this.closest('.photo-slot-wrap').remove()">×</button>
    `;
    grid.appendChild(wrap);
}

function previewNewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const label = input.closest('label');
            label.innerHTML = `<img src="${e.target.result}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                <input type="file" name="photos[]" accept="image/*" onchange="previewNewPhoto(this)" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function deletePhoto(photoId, btn) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'delete_photos[]';
    input.value = photoId;
    document.getElementById('deletePhotoInputs').appendChild(input);
    btn.closest('.existing-photo').remove();
}

function addReason() {
    const list = document.getElementById('reasonsList');
    const div = document.createElement('div');
    div.className = 'removable-item';
    div.innerHTML = `<input type="text" name="reasons[]" class="form-control" placeholder="Add a reason to join">
        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>`;
    list.appendChild(div);
}

function addTestimonial() {
    const list = document.getElementById('testimonialsList');
    const div = document.createElement('div');
    div.className = 'testimonial-wrap';
    div.innerHTML = `<textarea name="testimonials[]" class="form-control" rows="3" placeholder="Enter a testimonial"></textarea>
        <button type="button" class="remove-btn" onclick="removeItem(this)" title="Remove">×</button>`;
    list.appendChild(div);
}

function removeItem(btn) {
    btn.closest('.removable-item, .testimonial-wrap').remove();
}
</script>
@endpush
