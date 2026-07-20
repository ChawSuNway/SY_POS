<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.login') }} — {{ __('app.app_name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=4">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="logo">
            <img src="{{ main_logo_url() }}" alt="{{ __('app.app_name') }}"
                 style="width:100%;border-radius:12px;display:block;margin-bottom:6px">
            <div class="sub">{{ __('app.tagline') }}</div>
        </div>

        @if($errors->any())
            <div class="err-box">
                @foreach($errors->all() as $e)<div>⚠️ {{ $e }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="field">
                <label>{{ __('app.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@shweyee.test">
            </div>
            <div class="field">
                <label>{{ __('app.password') }}</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <div class="field">
                <label class="check"><input type="checkbox" name="remember" value="1"> {{ __('app.remember') }}</label>
            </div>
            <button type="submit" class="btn primary block lg">{{ __('app.login') }} →</button>
        </form>

        <div style="text-align:center;margin-top:18px">
            <div class="locale" style="display:inline-flex">
                <a href="{{ route('locale.switch','my') }}" class="{{ app()->getLocale()=='my'?'on':'' }}">မြန်မာ</a>
                <a href="{{ route('locale.switch','en') }}" class="{{ app()->getLocale()=='en'?'on':'' }}">EN</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
