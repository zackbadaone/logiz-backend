<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // الجدول الأول: الطلبيات (الفاتورة العامة)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete(); // ربط الطلبية بالمحل
            $table->decimal('total_amount', 15, 2)->default(0); // السعر الإجمالي للفاتورة
            $table->string('status')->default('pending'); // حالة الطلبية: pending, completed, cancelled
            $table->timestamps();
        });

        // الجدول الثاني: تفاصيل الطلبية (السلع داخل الفاتورة)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete(); // ربط السطر بالفاتورة
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // ربط السطر بالمنتج
            $table->integer('quantity'); // الكمية المباعة
            $table->decimal('price', 10, 2); // سعر المنتج وقت البيع
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};