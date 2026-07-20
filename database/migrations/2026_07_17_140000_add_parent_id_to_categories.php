<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categories ကို ၂ အဆင့် (parent → sub-category) ဖြစ်စေရန် parent_id ထည့်။
 * parent_id = null => ပင်မ အမျိုးအစား၊ တန်ဖိုးရှိ => sub-category။
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('shop_id')
                ->constrained('categories')->nullOnDelete();
        });

        // unique(shop,type,name) => unique(shop,type,parent_id,name)
        // (parent မတူလျှင် sub-category အမည်တူ ခွင့်ပြု — ရိုးရိုး/ထူးရှယ် ကို parent များစွာအောက်)
        // အသစ်ကို အရင်ထည့် (shop_id FK ၏ backing index ဖြစ်စေရန်)၊ ထို့နောက် အဟောင်း ဖျက်
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['shop_id', 'type', 'parent_id', 'name']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_shop_id_type_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'type', 'parent_id', 'name']);
            $table->unique(['shop_id', 'type', 'name']);
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
