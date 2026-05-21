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
                <img src="{{ asset('images/AppLogo.png') }}" alt="OrgFinder Logo" style="height:70px;width:auto;object-fit:contain;display:block;flex-shrink:0;margin-right:-16px;">
                <span class="brand-name">RGFINDER</span>
            </div>
            <p class="brand-sub">Manage and Track Organization</p>

            @if($errors->any())
                <div class="error-msg">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin-officer.login.post') }}">
                @csrf
                <div class="field">
                    <span class="field-icon">✉</span>
                    <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="field">
                    <span class="field-icon">🔒</span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="forgot"><a href="#">Forgot password?</a></div>
                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
