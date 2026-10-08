<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransaction extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للتعبئة والتعديل (Mass Assignable)
     */
    protected $fillable = [
        'driver_id',       // معرف السائق صاحب الجولة
        'type',            // نوع الحركة: 'loading' (شحن)، 'return' (مرتجع)، 'reconciliation' (تسوية كاش)
        'items',           // السلع المشحونة أو المرتجعة (ستحفظ كـ JSON)
        'declared_cash',   // الكاش المصرح به من النظام
        'comptage_cash',   // الكاش الفعلي الذي عده أمين المخزن
        'discrepancy',     // الفارق المالي (إن وجد)
        'status',          // حالة العملية: 'pending' (قيد المراجعة)، 'validated' (مؤكدة)
    ];

    /**
     * تحويل الحقول تلقائياً لتسهيل التعامل معها برمجياً (Casting)
     */
    protected $casts = [
        'items' => 'array', // تحويل حقل السلع تلقائياً من وإلى مصفوفة عند القراءة والكتابة
    ];
}