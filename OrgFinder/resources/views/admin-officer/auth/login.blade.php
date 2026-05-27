<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Officer - Login</title>
    <link rel="stylesheet" href="{{ asset('css/admin-officer/login.css') }}">
</head>
<body>
    <div class="center-wrap">
        <div class="card">
            <div class="brand">
                <span class="brand-name">NEU</span>
                <img src="{{ asset('images/AppLogo.png') }}" alt="NEUORG Logo" style="height:60px;width:auto;object-fit:contain;display:block;flex-shrink:0;margin-left:-20px;margin-right:-20px;">
                <span class="brand-name">RG</span>
            </div>
            <p class="brand-sub">Manage and Track Organization</p>

            @if($errors->any())
                <div class="error-msg">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin-officer.login.post') }}">
                @csrf
                <div class="field">
                    <span class="field-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
                    </span>
                    <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="field">
                    <span class="field-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="forgot"><a href="#">Forgot password?</a></div>
                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
