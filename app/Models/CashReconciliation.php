<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CashReconciliation extends Model
{
    /**
     * الحقول القابلة للتعبئة لحماية النظام من ثغرات الحقن (Mass Assignment)
     */
    protected $fillable = [
        'user_id',
        'sales_amount',
        'collected_debts',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'notes',
        'reporting_date'
    ];

    /**
     * تحويل الحقول البرمجية لضمان قراءتها كأرقام عشرية وتواريخ بدقة
     */
    protected $casts = [
        'sales_amount' => 'decimal:2',
        'collected_debts' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'reporting_date' => 'date',
    ];

    /**
     * علاقة السجل العكسية مع المستخدم (السائق) الذي تنتمي له التسوية
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * برمجة دالة المطابقة الرياضية التلقائية باستخدام أحداث الـ Eloquent Model
     * يتم حساب الكاش المتوقع والفارق والحالة آلياً قبل الحفظ أو التحديث
     */
    protected static function booted(): void
    {
        static::saving(function (CashReconciliation $reconciliation) {
            // 1. حساب الكاش المتوقع = (المبيعات الكاش + الديون المحصلة)
            $reconciliation->expected_cash = $reconciliation->sales_amount + $reconciliation->collected_debts;

            // 2. حساب الفارق المالي = (Kاش الفعلي المسلم - الكاش المتوقع)
            $reconciliation->difference = $reconciliation->actual_cash - $reconciliation->expected_cash;

            // 3. تحديد حالة المطابقة آلياً بناءً على الفارق
            if ($reconciliation->difference == 0) {
                $reconciliation->status = 'matched';
            } else {
                $reconciliation->status = 'mismatch';
            }
        });
    }

    /**
     * دالة مساعدة لمعرفة ما إذا كان هناك عجز مالي مسجل على ذمة السائق
     */
    public function hasDeficit(): bool
    {
        return $this->difference < 0;
    }

    /**
     * دالة مساعدة لمعرفة ما إذا كانت هناك زيادة مالية غير مبررة
     */
    public function hasSurplus(): bool
    {
        return $this->difference > 0;
    }

    /**
     * نطاق برميجي (Scope) لجلب كافة التسويات التي تحتوي على فروقات مالية فقط
     */
    public function scopeWithMismatches(Builder $query): Builder
    {
        return $query->where('status', 'mismatch');
    }
}