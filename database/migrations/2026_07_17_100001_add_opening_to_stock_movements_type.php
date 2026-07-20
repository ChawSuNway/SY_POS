<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * stock_movements.type enum ကို 'opening' (ဖွင့်လှစ်လက်ကျန်) ဖြင့် တိုးချဲ့သည်။
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('purchase','sale','adjustment','opening') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('purchase','sale','adjustment') NOT NULL");
    }
};
