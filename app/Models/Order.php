<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها جماعياً لحماية بيانات الفواتير والعمليات المالية
     */
    protected $fillable = [
        'store_id',
        'total_amount',
        'status',
    ];

    /**
     * تحويل تلقائي للأنواع البرمجية لضمان دقة الحسابات المالية الإجمالية
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * علاقة الطلبية بالمحل: الطلبية تنتمي لمحل واحد
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * علاقة الطلبية بالأسطر: الطلبية الواحدة تحتوي على عدة سلع أو تفاصيل
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}