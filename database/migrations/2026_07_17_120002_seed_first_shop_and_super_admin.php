<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * ရှိပြီးသား single-shop data ကို Shop #1 အဖြစ် ပြောင်း၊ ၀န်ထမ်းများ တွဲ၊ super admin ဖန်တီး။
     */
    public function up(): void
    {
        // settings table (single-shop branding) မှ တန်ဖိုးများ ဆွဲ — မရှိလျှင် default
        $get = function (string $key, $default = null) {
            $row = DB::table('settings')->where('key', $key)->value('value');
            return ($row === null || $row === '') ? $default : $row;
        };

        $shopId = DB::table('shops')->insertGetId([
            'name'       => $get('shop_name', 'ရွှေရည်'),
            'name_en'    => $get('shop_name_en', 'Shwe Yee'),
            'tagline'    => $get('shop_tagline'),
            'tagline_en' => $get('shop_tagline_en'),
            'logo'       => $get('shop_logo'),
            'phone'      => $get('shop_phone'),
            'address'    => $get('shop_address'),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ရှိပြီးသား ၀န်ထမ်း အားလုံး → Shop #1
        DB::table('users')->whereNull('shop_id')->update(['shop_id' => $shopId]);

        // Super Admin account (ဆိုင်မဲ့)
        if (! DB::table('users')->where('email', 'super@shweyee.test')->exists()) {
            DB::table('users')->insert([
                'name'       => 'Super Admin',
                'email'      => 'super@shweyee.test',
                'password'   => Hash::make('password'),
                'role'       => 'super_admin',
                'shop_id'    => null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'super@shweyee.test')->delete();
        DB::table('users')->update(['shop_id' => null]);
        DB::table('shops')->truncate();
    }
};
