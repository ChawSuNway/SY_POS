<?php

if (! function_exists('mmk')) {
    /** ကျပ်ငွေ format — 1,234,500 */
    function mmk($value): string
    {
        return number_format((float) $value, 0);
    }
}

if (! function_exists('qty_fmt')) {
    /** အရေအတွက် — နောက်ဆုံး သုည ဖြုတ်ပြ (e.g. 2.500 -> 2.5, 3.000 -> 3) */
    function qty_fmt($value): string
    {
        $s = number_format((float) $value, 3, '.', ',');
        if (str_contains($s, '.')) {
            $s = rtrim(rtrim($s, '0'), '.');
        }
        return $s;
    }
}

if (! function_exists('setting')) {
    /** ဆက်တင် တစ်ခု ဖတ်ရန် (fallback ပါ) */
    function setting(string $key, $default = null)
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (! function_exists('shop_name')) {
    /** ဆိုင်နာမည် — လက်ရှိ locale အလိုက်၊ မသတ်မှတ်ရသေးလျှင် lang default */
    function shop_name(): string
    {
        $key = app()->getLocale() === 'en' ? 'shop_name_en' : 'shop_name';

        return setting($key) ?? setting('shop_name') ?? __('app.app_name');
    }
}

if (! function_exists('shop_tagline')) {
    /** ဆိုင် tagline — locale အလိုက် */
    function shop_tagline(): string
    {
        $key = app()->getLocale() === 'en' ? 'shop_tagline_en' : 'shop_tagline';

        return setting($key) ?? setting('shop_tagline') ?? __('app.tagline');
    }
}

if (! function_exists('shop_logo_url')) {
    /** logo image URL — မရှိလျှင် null (emoji fallback သုံးရန်) */
    function shop_logo_url(): ?string
    {
        $path = setting('shop_logo');

        return $path ? asset($path) : null;
    }
}
