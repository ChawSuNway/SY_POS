@extends('layouts.app')
@section('title', __('app.reports'))

@section('content')
    <div class="grid cols-3">
        <a href="{{ route('reports.sales') }}">
            <div class="card">
                <div class="card-body">
                    <div style="font-size:2.2rem;margin-bottom:8px">🧾</div>
                    <h3>{{ __('app.sales_report') }}</h3>
                    <div class="muted small">{{ app()->getLocale()=='my' ? 'ကာလအလိုက် ရောင်းရငွေနှင့် အမြတ်' : 'Revenue & profit by date range' }}</div>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.purchases') }}">
            <div class="card">
                <div class="card-body">
                    <div style="font-size:2.2rem;margin-bottom:8px">📥</div>
                    <h3>{{ __('app.purchase_report') }}</h3>
                    <div class="muted small">{{ app()->getLocale()=='my' ? 'ကာလအလိုက် အဝယ်စရိတ်' : 'Purchase spending by date range' }}</div>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.profit') }}">
            <div class="card">
                <div class="card-body">
                    <div style="font-size:2.2rem;margin-bottom:8px">💰</div>
                    <h3>{{ __('app.profit_report') }}</h3>
                    <div class="muted small">{{ app()->getLocale()=='my' ? 'ကုန်ပစ္စည်း/တံဆိပ်/အမျိုးအစားအလိုက် အမြတ်' : 'Profit by product, brand or category' }}</div>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.inventory') }}">
            <div class="card">
                <div class="card-body">
                    <div style="font-size:2.2rem;margin-bottom:8px">📦</div>
                    <h3>{{ __('app.inventory_report') }}</h3>
                    <div class="muted small">{{ app()->getLocale()=='my' ? 'လက်ရှိ လက်ကျန်နှင့် တန်ဖိုး' : 'Current stock balance & value' }}</div>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.low_stock') }}">
            <div class="card">
                <div class="card-body">
                    <div style="font-size:2.2rem;margin-bottom:8px">⚠️</div>
                    <h3>{{ __('app.low_stock_report') }}</h3>
                    <div class="muted small">{{ app()->getLocale()=='my' ? 'ဖြည့်တင်းရန် လိုအပ်သော ပစ္စည်းများ' : 'Items that need restocking' }}</div>
                </div>
            </div>
        </a>
    </div>
@endsection
