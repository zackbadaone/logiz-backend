<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها جماعياً لحماية بيانات الكتالوج والمخزون
     */
    protected $fillable = [
        'name',
        'sku',
        'category',
        'price',
        'cost_price',
        'stock',
        'min_stock_alert',
        'image_url',
        'is_available',
    ];

    /**
     * تحويل تلقائي للأنواع البرمجية لضمان دقة الأسعار والكميات وحالة المنتج
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock_alert' => 'integer',
        'is_available' => 'boolean',
    ];

    /**
     * علاقة المنتج بحركات المخزون الخاصة به
     * المنتج الواحد لديه العديد من عمليات الدخول والخروج والتسويات المخزنية
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}