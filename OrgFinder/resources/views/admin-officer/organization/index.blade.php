@extends('admin-officer.layouts.app')

@section('title', 'Organization Profile')
@section('page-title', $organization->name ?? 'Organization')
@section('page-subtitle', 'Organization Profile')

@section('content')
@if($organization)

{{-- Daily Reminder --}}
@if($reminder)
<div style="background:#fff;border-radius:14px;padding:18px 20px;margin-bottom:20px;border-left:5px solid #4361EE;box-shadow:0 2px 10px rgba(67,97,238,0.08);">
    <p style="font-size:13.5px;color:#334155;line-height:1.7;margin:0 0 12px;">{{ $reminder }}</p>
    <div style="height:1px;background:#e2e8f0;margin-bottom:12px;"></div>
    <span style="font-size:12px;color:#64748b;font-style:italic;line-height:1.6;">"{{ $quote }}"</span>
</div>
@endif

{{-- Edit Profile button --}}
<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <a href="{{ route('admin-officer.organization.edit') }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#4361EE;color:#fff;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#fff"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.21a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
        Edit Profile
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- Left column --}}
    <div>
        {{-- Org info card --}}
        <div class="card" style="margin-bottom:20px;">
            <div style="display:flex;align-items:flex-start;gap:18px;">
                @if($organization->logo)
                    <img src="{{ asset('storage/'.$organization->logo) }}"
                         style="width:72px;height:72px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                @else
                    <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#7b6de8,#3b5fe2);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff;flex-shrink:0;">
                        {{ strtoupper(substr($organization->name, 0, 1)) }}
                    </div>
                @endif
                <div style="flex:1;min-width:0;">
                    <div style="font-size:16px;font-weight:700;color:#4361EE;margin-bottom:6px;">{{ $organization->name }}</div>
                    @if($organization->category)
                        <div style="font-size:12px;color:#64748b;margin-bottom:8px;">{{ implode(', ', $organization->category) }}</div>
                    @endif
                    <p style="font-size:13px;color:#64748b;line-height:1.6;">{{ $organization->mission ?? '' }}</p>
                    <div style="display:flex;flex-wrap:wrap;gap:16px;margin-top:12px;">
                        @if($organization->room_number)
                        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                            {{ $organization->room_number }}
                        </div>
                        @endif
                        @if($organization->contact_telegram)
                        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>
                            {{ $organization->contact_telegram }}
                        </div>
                        @endif
                        @if($organization->contact_facebook)
                        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            {{ $organization->contact_facebook }}
                        </div>
                        @endif
                        @if($organization->president)
                        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            {{ $organization->president }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Reasons to Join --}}
        @if($reasons->count())
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:15px;font-weight:700;color:#1e2f6e;margin-bottom:12px;">Reasons to Join</div>
            <ul style="padding-left:0;list-style:none;margin:0;">
                @foreach($reasons as $reason)
                <li style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="flex-shrink:0;width:22px;height:22px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="#4361EE"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    </span>
                    <span style="font-size:13px;color:#475569;line-height:1.6;">{{ $reason->reason }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Member/Alumni Testimonials --}}
        @if($testimonials->count())
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:15px;font-weight:700;color:#1e2f6e;margin-bottom:14px;">Member/Alumni Testimonials</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                @foreach($testimonials as $t)
                <div style="background:#f8f9ff;border-radius:10px;padding:14px 16px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#4361EE" style="margin-bottom:8px;opacity:.5;"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
                    <p style="font-size:12px;color:#475569;line-height:1.6;margin:0 0 8px;">{{ $t->testimonial }}</p>
                    @if($t->author)
                    <div style="font-size:11px;font-weight:700;color:#4361EE;">— {{ $t->author }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Event Highlights Carousel --}}
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:15px;font-weight:700;color:#1e2f6e;margin-bottom:14px;">Event Highlights</div>
            @if($eventPosters->count())
            <div style="position:relative;padding:0 14px;">
                <div id="eventTrack" style="display:flex;gap:14px;overflow:hidden;scroll-behavior:smooth;">
                    @foreach($eventPosters as $event)
                    <div style="flex:0 0 244px;height:316px;border-radius:12px;overflow:hidden;position:relative;background:#f1f5f9;cursor:pointer;"
                         onclick="openLightbox('{{ asset('storage/'.$event->event_poster) }}','{{ addslashes($event->title) }}')">
                        <img src="{{ asset('storage/'.$event->event_poster) }}" style="width:100%;height:100%;object-fit:cover;">
                        <div style="position:absolute;bottom:0;left:0;right:0;padding:10px 12px;background:linear-gradient(transparent,rgba(0,0,0,.55));">
                            <div style="color:#fff;font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $event->title }}</div>
                            <div style="color:rgba(255,255,255,.75);font-size:11px;">{{ \Carbon\Carbon::parse($event->date)->format('M j, Y') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button onclick="scrollTrack('eventTrack',-1)" style="position:absolute;left:0;top:50%;transform:translateY(-50%);width:32px;height:32px;border-radius:50%;border:none;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.15);cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#4361EE"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                </button>
                <button onclick="scrollTrack('eventTrack',1)" style="position:absolute;right:0;top:50%;transform:translateY(-50%);width:32px;height:32px;border-radius:50%;border:none;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.15);cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#4361EE"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </button>
            </div>
            @else
            <div style="text-align:center;padding:40px 20px;color:#94a3b8;font-size:13px;">
                No approved event posters yet.
            </div>
            @endif
        </div>
    </div>

    {{-- Right column --}}
    <div>
        @if($organization->vision)
        <div class="card" style="margin-bottom:16px;">
            <div style="font-size:13px;font-weight:800;color:#4361EE;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Vision</div>
            <p style="font-size:13px;color:#64748b;line-height:1.6;font-style:italic;">{{ $organization->vision }}</p>
        </div>
        @endif

        @if($organization->mission)
        <div class="card" style="margin-bottom:16px;">
            <div style="font-size:13px;font-weight:800;color:#4361EE;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Mission</div>
            <p style="font-size:13px;color:#64748b;line-height:1.6;font-style:italic;">{{ $organization->mission }}</p>
        </div>
        @endif

        {{-- Photos Coverflow Carousel --}}
        @if($photos->count())
        <div class="card" style="overflow:hidden;padding-top:16px;">
            <div style="position:relative;height:220px;">
                @foreach($photos as $i => $photo)
                <div class="cf-slide" data-index="{{ $i }}"
                     style="position:absolute;top:50%;left:50%;width:180px;height:180px;border-radius:14px;overflow:hidden;cursor:pointer;transition:transform .4s ease,opacity .4s ease,box-shadow .4s ease;"
                     onclick="cfClick({{ $i }})">
                    <img src="{{ asset('storage/'.$photo->photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
                </div>
                @endforeach
            </div>
            {{-- Dots --}}
            <div style="display:flex;justify-content:center;gap:6px;margin-top:10px;" id="cfDots">
                @foreach($photos as $i => $photo)
                <div onclick="cfGoTo({{ $i }})"
                     style="width:8px;height:8px;border-radius:50%;background:{{ $i===0?'#4361EE':'#cbd5e1' }};cursor:pointer;transition:background .2s;"></div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="closeLightbox()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:12px;">
    <img id="lightboxImg" src="" style="max-width:90vw;max-height:80vh;border-radius:12px;object-fit:contain;">
    <div id="lightboxCaption" style="color:#fff;font-size:14px;font-weight:500;"></div>
</div>

@else
<div class="card" style="text-align:center;padding:60px;">
    <p style="color:#94a3b8;font-size:14px;">No organization assigned to your account.</p>
</div>
@endif

@push('scripts')
<script>
// ── Event track scroll ──────────────────────────────────────────────
function scrollTrack(id, dir) {
    const track = document.getElementById(id);
    if (track) track.scrollLeft += dir * (244 + 14) * 2;
}

// ── Coverflow carousel ──────────────────────────────────────────────
let cfCurrent = 0;
const cfSlides = Array.from(document.querySelectorAll('.cf-slide'));
const cfTotal  = cfSlides.length;

function cfRender() {
    cfSlides.forEach((el, i) => {
        const offset = i - cfCurrent;
        const absOff = Math.abs(offset);
        const scale  = absOff === 0 ? 1 : absOff === 1 ? 0.72 : 0.55;
        const tx     = offset * 120;           // horizontal spread
        const ty     = absOff === 0 ? -50 : absOff === 1 ? -42 : -35;
        const opacity= absOff === 0 ? 1 : absOff === 1 ? 0.6 : 0.35;
        const z      = absOff === 0 ? 10 : absOff === 1 ? 5 : 1;
        const shadow = absOff === 0 ? '0 16px 40px rgba(0,0,0,.25)' : 'none';
        el.style.transform  = `translate(calc(-50% + ${tx}px), ${ty}%) scale(${scale})`;
        el.style.opacity    = opacity;
        el.style.zIndex     = z;
        el.style.boxShadow  = shadow;
    });
    // dots
    document.querySelectorAll('#cfDots div').forEach((d, i) => {
        d.style.background = i === cfCurrent ? '#4361EE' : '#cbd5e1';
        d.style.width  = i === cfCurrent ? '20px' : '8px';
        d.style.borderRadius = '4px';
    });
}

function cfGoTo(idx) {
    cfCurrent = Math.max(0, Math.min(cfTotal - 1, idx));
    cfRender();
}

function cfClick(idx) {
    if (idx === cfCurrent) {
        openLightbox(cfSlides[idx].querySelector('img').src, '');
    } else {
        cfGoTo(idx);
    }
}

// keyboard arrows
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft')  cfGoTo(cfCurrent - 1);
    if (e.key === 'ArrowRight') cfGoTo(cfCurrent + 1);
    if (e.key === 'Escape')     closeLightbox();
});

if (cfTotal > 0) {
    cfRender();
    if (cfTotal > 1) {
        setInterval(() => cfGoTo((cfCurrent + 1) % cfTotal), 2000);
    }
}

// ── Lightbox ────────────────────────────────────────────────────────
function openLightbox(src, caption) {
    const lb = document.getElementById('lightbox');
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxCaption').textContent = caption;
    lb.style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
</script>
@endpush
@endsection
