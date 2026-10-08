<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * جلب قائمة كل المنتجات مع الـ SKU، الأسعار، وحالة المخزون بصيغة JSON كمصفوفة مباشرة
     * مع محاذاة حقول tag و tone ديناميكياً للواجهة الأمامية
     */
    public function index(): JsonResponse
    {
        // جلب جميع المنتجات من قاعدة البيانات
        $products = Product::all();

        // تحويل وتجهيز المنتجات ديناميكياً لتوفير الحقول التي تتوقعها الواجهة الأمامية دون تعديل جدول الـ DB
        $mappedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                // الواجهة تتوقع tag، نقوم بمحاذاتها مع حقل category الحقيقي
                'tag' => $product->category ?? 'Stock',
                // الواجهة تتوقع tone، نحدد نغمة لونية افتراضية بناءً على توفر المخزون لتجنب الأخطاء البصرية
                'tone' => $product->stock > 0 ? 'ready' : 'damaged',
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });

        // إرجاع مصفوفة المنتجات المعدلة مباشرة لتتوافق مع دالة setCatalog(liveCatalog) في الـ Frontend
        return response()->json($mappedProducts, 200);
    }

    /**
     * استقبال بيانات المنتج الجديد من واجهة المبيعات وحفظه في قاعدة البيانات
     */
    public function store(Request $request): JsonResponse
    {
        // التحقق من البيانات المرسلة لضمان عدم تكرار الـ SKU في المخزن
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'tag' => 'nullable|string', // نستقبلها كما هي من الواجهة لضمان التوافق
            'tone' => 'nullable|string',
        ]);

        // إنشاء وحفظ المنتج الجديد في قاعدة البيانات مع مطابقة الحقول جراحياً
        $product = Product::create([
            'sku' => strtoupper($validated['sku']),
            'name' => $validated['name'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            // إصلاح الثغرة: حفظ الـ tag القادم من الواجهة داخل عمود category الحقيقي في قاعدة البيانات
            'category' => $validated['tag'] ?? 'Stock', 
            // حقل tone غير موجود في جدول قاعدة البيانات لذلك تم استبعاده من الحفظ المباشر لمنع خطأ SQL
        ]);

        // إرجاع الاستجابة مع دمج الحقول المحاذية لضمان تحديث الواجهة فوراً بدون إعادة تحميل
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل المنتج الجديد بنجاح في السيرفر المحلي ومحاذاة التصنيف والألوان ديناميكياً',
            'data' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'tag' => $product->category,
                'tone' => $product->stock > 0 ? 'ready' : 'damaged',
            ]
        ], 201);
    }
}