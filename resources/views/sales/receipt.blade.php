<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.receipt') }} — {{ $sale->invoice_no }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=4">
</head>
<body style="background:#e2e8f0;padding:20px">

<div class="no-print" style="max-width:320px;margin:0 auto 12px;display:flex;gap:8px">
    <button class="btn primary block" onclick="window.print()">🖨️ {{ __('app.print') }}</button>
    <a class="btn ghost" href="{{ route('sales.show',$sale) }}">← {{ __('app.back') }}</a>
</div>

<div class="receipt">
    @if(shop_logo_url())
        <div style="text-align:center;margin-bottom:6px"><img src="{{ shop_logo_url() }}" alt="" style="height:56px;object-fit:contain"></div>
        <h2>{{ shop_name() }}</h2>
    @else
        <h2>🌾 {{ shop_name() }}</h2>
    @endif
    <div class="r-sub">{{ shop_tagline() }}</div>
    @php $shop = current_shop(); @endphp
    @if($shop && ($shop->address || $shop->phone))
        <div class="r-sub" style="font-size:.72rem">
            @if($shop->address){{ $shop->address }}@endif
            @if($shop->phone) · ☎ {{ $shop->phone }}@endif
        </div>
    @endif

    <table>
        <tr><td>{{ __('app.invoice_no') }}</td><td style="text-align:right"><b>{{ $sale->invoice_no }}</b></td></tr>
        <tr><td>{{ __('app.date') }}</td><td style="text-align:right">{{ $sale->sold_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>{{ __('app.cashier') }}</td><td style="text-align:right">{{ $sale->user->name ?? '-' }}</td></tr>
        @if($sale->customer_name)
        <tr><td>{{ __('app.customer') }}</td><td style="text-align:right">{{ $sale->customer_name }}</td></tr>
        @endif
    </table>

    <div class="r-line"></div>

    <table>
        @foreach($sale->items as $it)
            <tr>
                <td colspan="2"><b>{{ $it->product->displayName() }}</b></td>
            </tr>
            <tr>
                <td>{{ qty_fmt($it->qty) }} {{ $it->unit_label }} × {{ mmk($it->unit_price) }}</td>
                <td style="text-align:right"><b>{{ mmk($it->line_total) }}</b></td>
            </tr>
        @endforeach
    </table>

    <div class="r-line"></div>

    <table>
        <tr><td>{{ __('app.subtotal') }}</td><td style="text-align:right">{{ mmk($sale->subtotal) }}</td></tr>
        @if($sale->discount > 0)
        <tr><td>{{ __('app.discount') }}</td><td style="text-align:right">-{{ mmk($sale->discount) }}</td></tr>
        @endif
        <tr style="font-size:1.05rem"><td><b>{{ __('app.total') }}</b></td><td style="text-align:right"><b>{{ mmk($sale->total) }} Ks</b></td></tr>
        <tr><td>{{ __('app.paid') }}</td><td style="text-align:right">{{ mmk($sale->paid_amount) }}</td></tr>
        <tr><td>{{ __('app.change') }}</td><td style="text-align:right">{{ mmk($sale->change_amount) }}</td></tr>
    </table>

    <div class="r-line"></div>
    <div style="text-align:center;font-size:.78rem;color:#475569;margin-top:8px">
        {{ app()->getLocale()=='my' ? 'ကျေးဇူးတင်ပါသည် — တစ်ဖန်ကြွရောက်ပါ' : 'Thank you — please come again' }}
    </div>
</div>

<script>
    // auto-focus print dialog on open if ?print=1
    @if(request('print'))window.addEventListener('load',()=>window.print());@endif
</script>
</body>
</html>
