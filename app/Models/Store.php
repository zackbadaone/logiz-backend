<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها جماعياً لحماية قاعدة البيانات (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'ar_name',        // 👈 أضفنا الاسم بالعربية
        'owner_name',
        'phone',
        'address',        // 👈 أضفنا العنوان ليكون متاحاً للحفظ
        'zone',           // 👈 أضفنا المنطقة للفلترة
        'debt',
        'debt_age_days',  // 👈 أضفنا أيام التأخير لحساب الخطورة
        'credit_limit',
        'status',
    ];

    /**
     * تحويل تلقائي للقيم المالية والرقمية لضمان دقة الحسابات والتحليلات
     */
    protected $casts = [
        'debt' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'debt_age_days' => 'integer', // 👈 تأكيد التعامل مع أيام التأخير كرقم صحيح
    ];

    /**
     * علاقة المحل مع الطلبيات: المحل الواحد يملك عدة طلبيات
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}