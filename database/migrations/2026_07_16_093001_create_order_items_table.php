<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_unit_id')->nullable()->constrained();
            $table->string('unit_label');
            $table->decimal('factor', 15, 4);
            $table->decimal('qty', 15, 3);
            $table->decimal('qty_base', 15, 3);
            $table->decimal('unit_price', 15, 2);          // မှာယူချိန် သဘောတူစျေး
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
