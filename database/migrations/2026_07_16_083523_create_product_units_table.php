<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');                              // အိတ် / ပြည် / ဗူး / ပုံး / ပိဿာ / ဆယ်သား
            $table->decimal('factor', 15, 4);                     // base-unit အရေအတွက် (၁ ယူနစ်လျှင်)
            $table->decimal('selling_price', 15, 2)->default(0);  // Admin သတ်မှတ် ရောင်းစျေး / ယူနစ်
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
