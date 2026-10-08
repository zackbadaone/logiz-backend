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
        Schema::table('stores', function (Blueprint $table) {
            // إضافة الحقول المطلوبة للتحليلات المتقدمة ببطء ونظام لضمان توافقها مع لوحة المدير
            if (!Schema::hasColumn('stores', 'ar_name')) {
                $table->string('ar_name')->nullable()->after('name'); // اسم المحل بالعربية
            }
            if (!Schema::hasColumn('stores', 'zone')) {
                $table->string('zone')->nullable()->after('address'); // المنطقة الجغرافية
            }
            if (!Schema::hasColumn('stores', 'debt_age_days')) {
                $table->integer('debt_age_days')->default(0)->after('debt'); // عدد أيام تأخر الدين
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // التراجع وحذف الحقول بأمان في حال تطلب الأمر ذلك
            $columns = [];
            if (Schema::hasColumn('stores', 'ar_name')) {
                $columns[] = 'ar_name';
            }
            if (Schema::hasColumn('stores', 'zone')) {
                $columns[] = 'zone';
            }
            if (Schema::hasColumn('stores', 'debt_age_days')) {
                $columns[] = 'debt_age_days';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};