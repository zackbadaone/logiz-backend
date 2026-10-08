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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المحل التجاري بالفرنسية (مثل: Supermarché El Baraka)
            $table->string('ar_name')->nullable(); // اسم المحل التجاري بالعربية (مثل: سوبرماركت البركة) 👈 مضاف للتحليلات المتقدمة
            $table->string('owner_name')->nullable(); // اسم صاحب المحل 
            $table->string('phone')->nullable(); // رقم الهاتف للاتصال
            $table->string('address')->nullable(); // العنوان الفعلي أو الجغرافي للمحل
            $table->string('zone')->nullable(); // المنطقة الجغرافية للمحل (مثل: Alger Centre) 👈 مضاف لفلترة الكاش والديون
            $table->decimal('debt', 15, 2)->default(0); // الدين الحالي المترتب على المحل
            $table->integer('debt_age_days')->default(0); // عدد أيام تأخر الدين الحالية 👈 مضاف لحساب مستويات الخطورة (Risk Assessment)
            $table->decimal('credit_limit', 15, 2)->default(50000); // سقف الدين المسموح به (Plafond)
            $table->string('status')->default('active'); // حالة المحل: active أو blocked
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};