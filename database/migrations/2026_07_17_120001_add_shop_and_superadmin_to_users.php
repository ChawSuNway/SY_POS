<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // role enum ကို super_admin ဖြင့် တိုးချဲ့
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('cashier','manager','admin','super_admin') NOT NULL DEFAULT 'cashier'");

        Schema::table('users', function (Blueprint $table) {
            // super_admin => null (ဆိုင်မဲ့)၊ ဆိုင်၀န်ထမ်း => မိမိဆိုင်
            $table->foreignId('shop_id')->nullable()->after('role')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('cashier','manager','admin') NOT NULL DEFAULT 'cashier'");
    }
};
