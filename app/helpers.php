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

if (! function_exists('current_shop')) {
    /** login ၀င်ထားသူ၏ လက်ရှိ ဆိုင် (super_admin => null)။ request တစ်ခုအတွင်း cache */
    function current_shop(): ?\App\Models\Shop
    {
        static $cache = [];
        $id = current_shop_id();
        if (! $id) {
            return null;
        }
        if (! array_key_exists($id, $cache)) {
            $cache[$id] = \App\Models\Shop::find($id);
        }
        return $cache[$id];
    }
}

if (! function_exists('current_shop_id')) {
    /**
     * လက်ရှိ ဆိုင် id။
     * - ဝန်ထမ်း (admin/manager/cashier) => မိမိ၏ shop_id
     * - Super Admin => session တွင် ရွေးထားသော ဆိုင် (မရွေးရသေးလျှင် null)
     *   ⇒ ရွေးပြီးလျှင် BelongsToShop scope + RequireShop အားလုံး ထိုဆိုင်အတိုင်း အလုပ်လုပ်၊
     *     Super Admin သည် ဆိုင်တိုင်းကို စီမံနိုင်သည်။
     */
    function current_shop_id(): ?int
    {
        $u = auth()->user();
        if (! $u) {
            return null;
        }

        if ($u->role === \App\Models\User::ROLE_SUPER_ADMIN) {
            $id = session('sa_shop_id');
            return $id ? (int) $id : null;
        }

        return $u->shop_id;
    }
}

if (! function_exists('shop_name')) {
    /** ဆိုင်နာမည် — လက်ရှိ locale အလိုက်၊ ဆိုင်မရှိလျှင် lang default */
    function shop_name(): string
    {
        return current_shop()?->displayName() ?: __('app.app_name');
    }
}

if (! function_exists('shop_tagline')) {
    /** ဆိုင် tagline — locale အလိုက် */
    function shop_tagline(): string
    {
        return current_shop()?->displayTagline() ?: __('app.tagline');
    }
}

if (! function_exists('shop_logo_url')) {
    /** logo image URL — မရှိလျှင် null (emoji fallback သုံးရန်) */
    function shop_logo_url(): ?string
    {
        return current_shop()?->logoUrl();
    }
}

if (! function_exists('main_logo_url')) {
    /** Platform (R&O POS) main logo — login + super admin တွင် သုံး */
    function main_logo_url(): string
    {
        return asset('img/rno-logo.png');
    }
}
