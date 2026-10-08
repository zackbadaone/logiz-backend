<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseTransaction; // 👈 استدعاء المهندس المعماري (النموذج) للتحدث مع قاعدة البيانات

class WarehouseController extends Controller
{
    /**
     * جلب قائمة المنتجات المراد شحنها صباحاً
     */
    public function getLoadingItems()
    {
        // بيانات مبدئية لتغذية الواجهة الأمامية (MVP) ريثما نربطها بقاعدة البيانات الخاصة بالمنتجات
        $items = [
            ["sku" => "SAV-LIFE-5L", "name" => "Life Savon Liquide 5L", "planned" => 40, "loaded" => 40, "unit" => "Cartons"],
            ["sku" => "HUI-AFIA-5L", "name" => "Huile Afia 5L", "planned" => 24, "loaded" => 24, "unit" => "Cartons"],
            ["sku" => "LAI-CAND-1L", "name" => "Lait Candia UHT 1L x12", "planned" => 30, "loaded" => 28, "unit" => "Fardeaux"],
            ["sku" => "EAU-IFRI-1L", "name" => "Ifri Eau Minérale 1.5L x6", "planned" => 60, "loaded" => 60, "unit" => "Fardeaux"],
            ["sku" => "JUS-RAMY-1L", "name" => "Jus Ramy 1L x12", "planned" => 20, "loaded" => 0, "unit" => "Fardeaux"],
        ];

        return response()->json($items, 200);
    }

    /**
     * محاكاة قراءة كود QR الخاص بالسائق لجلب بيانات المرتجعات والكاش
     */
    public function simulateDriverScan()
    {
        $reconciliationData = [
            "driverId" => "DZ-23-014",
            "declaredCash" => 85000,
            "returns" => [
                ["name" => "Lait Candia UHT 1L x12", "qty" => 2, "type" => "good"],
                ["name" => "Huile Afia 5L (Bidon percé)", "qty" => 1, "type" => "damaged"],
                ["name" => "Bimo Tartina x24", "qty" => 3, "type" => "good"],
            ]
        ];

        return response()->json($reconciliationData, 200);
    }

    /**
     * استقبال وتأكيد بيانات الشحن الصباحي من أمين المخزن وحفظها في قاعدة البيانات
     */
    public function validateLoading(Request $request)
    {
        $validatedData = $request->validate([
            'items' => 'required|array',
            'progress' => 'required|integer',
        ]);

        // حفظ الشحنة في قاعدة البيانات بشكل حي ودائم
        WarehouseTransaction::create([
            'driver_id' => 'DEPOT-001', // معرف افتراضي حتى يتم ربط النظام بحسابات السائقين
            'type' => 'loading',
            'items' => $validatedData['items'],
            'declared_cash' => 0,
            'comptage_cash' => 0,
            'discrepancy' => 0,
            'status' => 'validated',
        ]);
        
        return response()->json([
            "message" => "تم تأكيد عملية الشحن وحفظ السجل في قاعدة البيانات بنجاح",
            "status" => "success",
            "data" => $validatedData
        ], 200);
    }

    /**
     * إغلاق اليوم ومطابقة الكاش والمرتجعات وتسجيلها في السيرفر
     */
    public function closeDay(Request $request)
    {
        $validatedData = $request->validate([
            'driverId' => 'required|string',
            'declaredCash' => 'required|numeric',
            'comptageCash' => 'required|numeric',
            'returns' => 'present|array',
            'hasMismatch' => 'required|boolean',
        ]);

        // حساب الفارق المالي رياضياً
        $discrepancy = $validatedData['comptageCash'] - $validatedData['declaredCash'];

        // تسجيل الفروقات المالية والمطابقة في قاعدة البيانات لتراها الإدارة
        WarehouseTransaction::create([
            'driver_id' => $validatedData['driverId'],
            'type' => 'reconciliation',
            'items' => $validatedData['returns'],
            'declared_cash' => $validatedData['declaredCash'],
            'comptage_cash' => $validatedData['comptageCash'],
            'discrepancy' => $discrepancy,
            // إذا كان هناك فارق، تُسجل العملية كـ قيد المراجعة للمدير
            'status' => $validatedData['hasMismatch'] ? 'pending_review' : 'validated',
        ]);

        return response()->json([
            "message" => "تم إغلاق اليوم ومزامنة البيانات في السيرفر بنجاح",
            "status" => "success",
            "discrepancy" => $discrepancy
        ], 200);
    }
}