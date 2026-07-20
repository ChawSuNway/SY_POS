<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();              // မှာယူ နံပါတ်
            $table->date('order_date');
            $table->date('delivery_date')->nullable();         // ပေးပို့သည့်ရက်
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();       // snapshot
            $table->foreignId('user_id')->constrained();       // မှာယူလက်ခံသူ
            $table->foreignId('delivered_by')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);       // ခန့်မှန်း (မှာယူချိန်)
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete(); // delivered ⇒ sale
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
