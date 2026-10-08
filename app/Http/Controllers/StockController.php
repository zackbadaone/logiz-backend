<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * جلب سجل حركات المخزون بالكامل مع معلومات المنتجات
     */
    public function index()
    {
        $movements = StockMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $movements
        ], 200);
    }

    /**
     * إجراء تسوية مخزنية (Adjustment) لمنتج معين وتعديل كميته الفعلية
     */
    public function adjust(Request $request)
    {
        // التحقق من البيانات المدخلة
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        try {
            // استخدام الـ Transaction لضمان حفظ العمليتين معاً أو إلغائهما عند حدوث خطأ
            $movement = DB::transaction(function () use ($request) {
                $product = Product::findOrFail($request->product_id);
                
                // حساب الفرق بين الكمية الجديدة والقديمة لتسجيله في الحركة
                $oldQuantity = $product->stock;
                $difference = $request->new_quantity - $oldQuantity;

                // إذا لم يكن هناك أي تغيير في الكمية، نخرج مباشرة
                if ($difference === 0) {
                    throw new \Exception('الكمية الجديدة تطابق الكمية الحالية بالفعل.');
                }

                // 1. تحديث الكمية الفعلية للمنتج في المستودع
                $product->stock = $request->new_quantity;
                $product->save();

                // 2. تسجيل حركة التسوية في جدول حركات المخزون لتوثيق العملية تاريخياً
                return StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity' => $difference, // سيوثق بالزائد أو بالناقص لمعرفة اتجاه التسوية
                    'reference' => 'تسوية مخزنية: ' . $request->reason . ' (من ' . $oldQuantity . ' إلى ' . $request->new_quantity . ')',
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'تمت تسوية مخزون المنتج وتحديث الكمية الفعلية بنجاح.',
                'data' => $movement->load('product')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}