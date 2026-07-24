<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1668e3">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/rno-logo.png') }}">
    <title>@yield('title', shop_name()) — {{ shop_name() }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=7">
</head>
<body>
@php $u = auth()->user(); @endphp
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            @php $cShop = current_shop(); @endphp
            @if(! $cShop)
                {{-- Super Admin / platform --}}
                <div style="background:#fff;border-radius:10px;padding:8px;display:flex;align-items:center;justify-content:center">
                    <img src="{{ main_logo_url() }}" alt="{{ __('app.app_name') }}" style="width:100%;display:block">
                </div>
            @elseif($cShop->logoUrl())
                <b style="display:flex;align-items:center;gap:8px">
                    <img src="{{ $cShop->logoUrl() }}" alt="" style="height:26px;width:26px;object-fit:contain;border-radius:6px">
                    {{ shop_name() }}
                </b>
                <span>{{ shop_tagline() }}</span>
            @else
                <b>🌾 {{ shop_name() }}</b>
                <span>{{ shop_tagline() }}</span>
            @endif
        </div>
        <nav>
            @php $r = Route::currentRouteName(); @endphp
            <a class="nav-link {{ $r=='dashboard'?'active':'' }}" href="{{ route('dashboard') }}">
                <span class="ic">📊</span> {{ __('app.dashboard') }}</a>

            @if($u->isSuperAdmin())
            {{-- Super Admin — ဆိုင်များ စီမံသာ --}}
            <div class="nav-group">{{ __('app.administration') }}</div>
            <a class="nav-link {{ str_starts_with($r,'shops.')?'active':'' }}" href="{{ route('shops.index') }}">
                <span class="ic">🏬</span> {{ __('app.shops') }}</a>
            @else

            <div class="nav-group">{{ __('app.pos') }}</div>
            <a class="nav-link {{ $r=='sales.create'?'active':'' }}" href="{{ route('sales.create') }}">
                <span class="ic">🛒</span> {{ __('app.pos') }}</a>
            <a class="nav-link {{ $r=='sales.index'?'active':'' }}" href="{{ route('sales.index') }}">
                <span class="ic">🧾</span> {{ __('app.sales') }}</a>
            <a class="nav-link {{ str_starts_with($r,'orders.')?'active':'' }}" href="{{ route('orders.index') }}">
                <span class="ic">📋</span> {{ __('app.orders') }}</a>
            <a class="nav-link {{ str_starts_with($r,'debts.')?'active':'' }}" href="{{ route('debts.receivable') }}">
                <span class="ic">💳</span> {{ __('app.debts') }}</a>
            <a class="nav-link {{ str_starts_with($r,'customers.')?'active':'' }}" href="{{ route('customers.index') }}">
                <span class="ic">🧑</span> {{ __('app.customers') }}</a>

            @if($u->hasAtLeast('manager'))
            <div class="nav-group">{{ __('app.inventory') }} / {{ __('app.reports') }}</div>
            <a class="nav-link {{ $r=='purchases.index'||$r=='purchases.create'||$r=='purchases.show'?'active':'' }}" href="{{ route('purchases.index') }}">
                <span class="ic">📥</span> {{ __('app.purchases') }}</a>
            <a class="nav-link {{ str_starts_with($r,'suppliers.')?'active':'' }}" href="{{ route('suppliers.index') }}">
                <span class="ic">🚚</span> {{ __('app.suppliers') }}</a>
            <a class="nav-link {{ str_starts_with($r,'products.')?'active':'' }}" href="{{ route('products.index') }}">
                <span class="ic">📦</span> {{ __('app.products') }}</a>
            <a class="nav-link {{ str_starts_with($r,'opening-stock')?'active':'' }}" href="{{ route('opening-stock.index') }}">
                <span class="ic">🏁</span> {{ __('app.opening_stock') }}</a>
            <a class="nav-link {{ str_starts_with($r,'losses.')?'active':'' }}" href="{{ route('losses.index') }}">
                <span class="ic">⚠️</span> {{ __('app.losses') }}</a>
            <a class="nav-link {{ str_starts_with($r,'expenses.')?'active':'' }}" href="{{ route('expenses.index') }}">
                <span class="ic">💸</span> {{ __('app.expenses') }}</a>
            <a class="nav-link {{ str_starts_with($r,'reports.')?'active':'' }}" href="{{ route('reports.index') }}">
                <span class="ic">📈</span> {{ __('app.reports') }}</a>
            @endif

            @if($u->isAdmin())
            <div class="nav-group">{{ __('app.settings') }}</div>
            <a class="nav-link {{ str_starts_with($r,'categories.')?'active':'' }}" href="{{ route('categories.index') }}">
                <span class="ic">🏷️</span> {{ __('app.categories') }}</a>
            <a class="nav-link {{ str_starts_with($r,'brands.')?'active':'' }}" href="{{ route('brands.index') }}">
                <span class="ic">🔖</span> {{ __('app.brands') }}</a>
            <a class="nav-link {{ str_starts_with($r,'users.')?'active':'' }}" href="{{ route('users.index') }}">
                <span class="ic">👥</span> {{ __('app.users') }}</a>
            <a class="nav-link {{ str_starts_with($r,'settings.')?'active':'' }}" href="{{ route('settings.edit') }}">
                <span class="ic">🏪</span> {{ __('app.shop_settings') }}</a>
            @endif
            @endif
        </nav>
        <div class="foot">
            {{ $u->roleLabel() }}<br>
            <span style="opacity:.7">v1.0 · {{ now()->format('Y') }}</span>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button class="btn ghost sm no-print" onclick="toggleSidebar()" style="display:none" id="menuBtn">☰</button>
                <div class="page-title">@yield('title', __('app.dashboard'))</div>
            </div>
            <div class="right">
                <div class="locale">
                    <a href="{{ route('locale.switch','my') }}" class="{{ app()->getLocale()=='my'?'on':'' }}">မြန်မာ</a>
                    <a href="{{ route('locale.switch','en') }}" class="{{ app()->getLocale()=='en'?'on':'' }}">EN</a>
                </div>
                <div class="userchip">
                    <div class="avatar">{{ mb_substr($u->name,0,1) }}</div>
                    <div class="meta">
                        <b>{{ $u->name }}</b>
                        <span>{{ $u->roleLabel() }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn ghost sm" type="submit">{{ __('app.logout') }} →</button>
                </form>
            </div>
        </header>

        <div class="content">
            @if(session('success'))
                <div class="alert success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert error">⛔ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="err-box">
                    <b>⚠️ {{ __('app.error') ?? 'Error' }}</b>
                    <ul style="margin:6px 0 0;padding-left:18px">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
<script>
    window.CSRF = document.querySelector('meta[name=csrf-token]').content;

    // ---- responsive sidebar (tablet: off-canvas + overlay) ----
    const _sb = document.getElementById('sidebar');
    const _ov = document.getElementById('sidebarOverlay');
    function toggleSidebar(){ _sb.classList.toggle('open'); _ov.classList.toggle('show', _sb.classList.contains('open')); }
    function closeSidebar(){ _sb.classList.remove('open'); _ov.classList.remove('show'); }
    function syncMenuBtn(){
        const small = window.innerWidth <= 1024;
        document.getElementById('menuBtn').style.display = small ? 'inline-flex' : 'none';
        if(!small) closeSidebar();
    }
    syncMenuBtn();
    window.addEventListener('resize', syncMenuBtn);
    // nav link နှိပ်လျှင် sidebar ပိတ် (tablet)
    _sb.querySelectorAll('a.nav-link').forEach(a=>a.addEventListener('click', ()=>{ if(window.innerWidth<=1024) closeSidebar(); }));
</script>
@stack('scripts')
</body>
</html>
