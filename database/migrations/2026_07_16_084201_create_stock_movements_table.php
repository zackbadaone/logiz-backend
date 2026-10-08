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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            
            // ربط الحركة بالمنتج المعني
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // نوع الحركة المخزنية: 
            // in = دخول (شراء/توريد للمستودع)
            // out = خروج (شحن صباحي مع سائق / مبيعات)
            // adjustment = تعديل (تسوية الفروقات أثناء الجرد)
            $table->enum('type', ['in', 'out', 'adjustment'])->default('in');
            
            // كمية الحركة (سواء موجبة أو سالبة في المنطق البرمجي)
            $table->integer('quantity');
            
            // مرجع توضيحي (مثال: "شحن جولة الصباح"، "فاتورة رقم #102"، "فروقات جرد المستودع")
            $table->string('reference')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};