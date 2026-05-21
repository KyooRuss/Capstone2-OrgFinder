@extends('admin-officer.layouts.app')

@section('title', 'Organization Profile')
@section('page-title', $organization->name ?? 'Organization')
@section('page-subtitle', 'Organization Profile')

@section('content')
@if($organization)
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
                        <div style="font-size:12px;color:#64748b;margin-bottom:8px;">{{ $organization->category }}</div>
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

        {{-- Reasons to Join (using mission bullets if available) --}}
        @if($organization->mission)
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:15px;font-weight:700;color:#1e2f6e;margin-bottom:12px;">Reasons to Join</div>
            <ul style="padding-left:18px;font-size:13px;color:#475569;line-height:2;">
                @foreach(array_filter(explode("\n", $organization->mission)) as $point)
                    @if(trim($point))
                        <li>{{ ltrim(trim($point), '-• ') }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
        @endif
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
        <div class="card">
            <div style="font-size:13px;font-weight:800;color:#4361EE;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Mission</div>
            <p style="font-size:13px;color:#64748b;line-height:1.6;font-style:italic;">{{ $organization->mission }}</p>
        </div>
        @endif
    </div>

</div>
@else
<div class="card" style="text-align:center;padding:60px;">
    <p style="color:#94a3b8;font-size:14px;">No organization assigned to your account.</p>
</div>
@endif
@endsection
