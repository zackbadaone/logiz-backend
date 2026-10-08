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
        Schema::create('cash_reconciliations', function (Blueprint $table) {
            $table->id();
            
            // ربط السجل بالسائق (المستخدم) الذي قام بالجولة
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // البيانات المالية المقدرة من النظام لليوم
            $table->decimal('sales_amount', 12, 2)->default(0.00); // إجمالي مبيعات الجولة الكاش
            $table->decimal('collected_debts', 12, 2)->default(0.00); // الديون القديمة التي حصلها اليوم
            $table->decimal('expected_cash', 12, 2)->default(0.00); // الكاش المتوقع تسليمه = المبيعات + الديون المحصلة
            
            // المطابقة الفعلية عند أمين الصندوق
            $table->decimal('actual_cash', 12, 2)->default(0.00); // الكاش الفعلي الذي سلمه السائق يدوياً
            $table->decimal('difference', 12, 2)->default(0.00); // الفارق المالي آلياً (actual_cash - expected_cash)
            
            // الحالات والأرشفة
            $table->enum('status', ['matched', 'mismatch', 'pending'])->default('pending'); // حالة المطابقة
            $table->text('notes')->nullable(); // ملاحظات أمين الصندوق في حال وجود عجز أو زيادة
            
            $table->date('reporting_date'); // تاريخ اليوم المحاسبي المالي
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');
    }
};