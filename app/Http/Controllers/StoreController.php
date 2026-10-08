<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    /**
     * جلب قائمة كل المحلات مع ديونها والـ Limit بصيغة JSON مباشرة كمصفوفة
     */
    public function index(): JsonResponse
    {
        // جلب جميع المحلات من قاعدة البيانات لملائمتها مع كود الواجهة
        $stores = Store::all();

        // إرجاع مصفوفة البيانات مباشرة لتتوافق مع دالة setStores(liveStores) في الـ Frontend
        return response()->json($stores, 200);
    }

    /**
     * استقبال بيانات الزبون الجديد من واجهة المبيعات وحفظه في قاعدة البيانات
     */
    public function store(Request $request): JsonResponse
    {
        // التحقق الصارم من البيانات القادمة من الفونتد لضمان عدم التكرار
        $validated = $request->validate([
            'name' => 'required|string|unique:stores,name',
            'debt' => 'nullable|numeric',
            'limit' => 'nullable|numeric', // نستقبلها بـ limit من الواجهة الأمامية كما هي
        ]);

        // إنشاء وتخزين المحل الجديد في قاعدة البيانات مع ربطه بالعمود الصحيح محاسبياً
        $store = Store::create([
            'name' => $validated['name'],
            'debt' => $validated['debt'] ?? 0,
            'credit_limit' => $validated['limit'] ?? 50000, // إصلاح الثغرة: الربط بالعمود الحقيقي credit_limit في قاعدة البيانات
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الزبون الجديد بنجاح في السيرفر المحلي وتحديث سقف الدين',
            'data' => $store
        ], 201);
    }
}