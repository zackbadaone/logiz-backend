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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المنتج (مثل: Coca-Cola 1.5L)
            $table->string('sku')->unique(); // الرمز الفريد للمنتج أو الباركود (Stock Keeping Unit)
            $table->string('category')->nullable(); // الفئة (مشروبات، عجائن، إلخ)
            $table->decimal('price', 10, 2); // سعر البيع للمحلات (Prix de vente)
            $table->decimal('cost_price', 10, 2)->nullable(); // سعر التكلفة لحساب صافي الأرباح لاحقاً
            $table->integer('stock')->default(0); // الكمية الحالية المتوفرة في المستودع
            $table->integer('min_stock_alert')->default(5); // الحد الأدنى للتنبيه بنفاذ المخزون
            $table->string('image_url')->nullable(); // رابط صورة المنتج
            $table->boolean('is_available')->default(true); // هل المنتج متوفر للبيع حالياً أم معلق
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};