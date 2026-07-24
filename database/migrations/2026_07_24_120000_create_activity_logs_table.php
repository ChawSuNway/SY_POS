<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User လုပ်ဆောင်ချက် မှတ်တမ်း — write request (create/update/delete) တိုင်းကို
 * terminable middleware က တစ်ကြောင်းစီ ထည့်သည်။ Super Admin ကသာ ကြည့်နိုင်။
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->unsignedBigInteger('shop_id')->nullable()->index();
            $table->string('action', 80)->index();      // route name (products.store …)
            $table->string('method', 10);               // POST / PUT / DELETE …
            $table->string('path', 255);
            $table->string('subject_type', 50)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
