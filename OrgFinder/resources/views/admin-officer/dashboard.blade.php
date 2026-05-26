@extends('admin-officer.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, {{ auth()->user()->name }}')

@push('styles')
<style>
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 8px rgba(67,97,238,0.07);
    border-left: 4px solid transparent;
}
.stat-card.blue   { border-left-color: #4361EE; }
.stat-card.green  { border-left-color: #22c55e; }
.stat-card.orange { border-left-color: #f59e0b; }
.stat-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.stat-icon.blue   { background: #eff1fe; }
.stat-icon.green  { background: #dcfce7; }
.stat-icon.orange { background: #fef3c7; }
.stat-icon svg    { width: 22px; height: 22px; }
.stat-value { font-size: 28px; font-weight: 800; color: #1e2f6e; line-height: 1; }
.stat-label { font-size: 12px; color: #94a3b8; font-weight: 500; margin-top: 3px; }

.reminder-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px 26px;
    box-shadow: 0 2px 12px rgba(67,97,238,0.09);
    border-left: 5px solid #4361EE;
}
.reminder-top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}
.reminder-badge {
    width: 36px; height: 36px; border-radius: 50%;
    background: #4361EE;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.reminder-badge svg { width: 18px; height: 18px; fill: #fff; }
.reminder-heading { font-size: 16px; font-weight: 800; color: #1e2f6e; }
.reminder-position {
    font-size: 12px; font-weight: 600;
    color: #4361EE;
    background: #eff1fe;
    padding: 3px 10px; border-radius: 20px;
    margin-left: auto;
}
.reminder-text {
    font-size: 14px; color: #334155; line-height: 1.7;
    margin-bottom: 16px;
}
.reminder-divider {
    height: 1px; background: #e2e8f0; margin-bottom: 14px;
}
.quote-row {
    display: flex; align-items: flex-start; gap: 8px;
}
.quote-icon { flex-shrink: 0; margin-top: 1px; }
.quote-icon svg { width: 16px; height: 16px; fill: #4361EE; }
.quote-text {
    font-size: 12.5px; color: #64748b;
    font-style: italic; line-height: 1.6;
}
</style>
@endpush

@section('content')

{{-- Stats row --}}
<div class="stats-row">
    <div class="stat-card blue">
        <div class="stat-icon blue">
            <svg viewBox="0 0 24 24" fill="#4361EE"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $membersCount }}</div>
            <div class="stat-label">Total Members</div>
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="#16a34a"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $eventsCount }}</div>
            <div class="stat-label">Total Events</div>
        </div>
    </div>

    <div class="stat-card orange">
        <div class="stat-icon orange">
            <svg viewBox="0 0 24 24" fill="#d97706"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $pendingCount }}</div>
            <div class="stat-label">Pending Applications</div>
        </div>
    </div>
</div>

{{-- Daily Reminder --}}
<div class="reminder-card">
    <div class="reminder-top">
        <div class="reminder-badge">
            <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
        </div>
        <span class="reminder-heading">Daily Reminder</span>
        @if($rawPosition)
            <span class="reminder-position">{{ $positionLabel }}</span>
        @endif
    </div>
    <p class="reminder-text">{{ $responsibility }}</p>
    <div class="reminder-divider"></div>
    <div class="quote-row">
        <span class="quote-icon">
            <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
        </span>
        <span class="quote-text">"{{ $quote }}"</span>
    </div>
</div>

@endsection
