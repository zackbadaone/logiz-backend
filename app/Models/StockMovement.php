<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    /**
     * الحقول المسموح بتعبئتها جماعياً لحماية النظام من ثغرات الحقن (Mass Assignment)
     */
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference',
    ];

    /**
     * علاقة الحركة المخزنية بالمنتج المعني
     * كل حركة مخزنية تنتمي لمنتج واحد محدد في الكتالوج
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}