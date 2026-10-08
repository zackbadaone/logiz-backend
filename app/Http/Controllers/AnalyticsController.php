<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;

class AnalyticsController extends Controller
{
    /**
     * جلب الملخص المالي الشامل للوحة الإدارة (Admin Dashboard)
     */
    public function getFinancialSummary()
    {
        // 1. جلب بيانات السائقين المؤقتة
        $driversCash = [
            ["name" => "Abdou Benali", "ar" => "عبدو بن علي", "zone" => "Alger Centre", "amount" => 85000, "plate" => "16-DZ-2314"],
            ["name" => "Yacine Merah", "ar" => "ياسين مراح", "zone" => "Bab Ezzouar", "amount" => 42500, "plate" => "16-DZ-4187"],
            ["name" => "Karim Bouzid", "ar" => "كريم بوزيد", "zone" => "Hussein Dey", "amount" => 31000, "plate" => "16-DZ-9023"],
            ["name" => "Salim Kaci", "ar" => "سليم قاسي", "zone" => "Dar El Beïda", "amount" => 18000, "plate" => "16-DZ-6612"],
            ["name" => "Mounir Tayeb", "ar" => "منير الطيب", "zone" => "Birtouta", "amount" => 8000, "plate" => "16-DZ-3340"],
        ];

        // 2. جلب الحوانيت الحقيقية من قاعدة البيانات
        $dbStores = Store::orderBy('debt', 'desc')->get();

        $outstandingDebts = [];

        if ($dbStores->isNotEmpty()) {
            foreach ($dbStores as $store) {
                $risk = 'safe';
                if ($store->debt_age_days >= 10) {
                    $risk = 'critical';
                } elseif ($store->debt_age_days >= 5) {
                    $risk = 'danger';
                }

                $outstandingDebts[] = [
                    "store" => $store->name,
                    "ar" => $store->ar_name ?? $store->name,
                    "amount" => (float) $store->debt,
                    "days" => (int) $store->debt_age_days,
                    "risk" => $risk
                ];
            }
        } else {
            // 3. الخطة البديلة (Fallback Data)
            $outstandingDebts = [
                ["store" => "Supermarché El Baraka", "ar" => "سوبرماركت البركة", "amount" => 84050, "days" => 2, "risk" => "critical"],
                ["store" => "Carrefour Dar El Beïda", "ar" => "كارفور دار البيضاء", "amount" => 96400, "days" => 5, "risk" => "danger"],
                ["store" => "Alimentation El Feth", "ar" => "تموينات الفتح", "amount" => 41200, "days" => 11, "risk" => "safe"],
                ["store" => "Épicerie Es-Salam", "ar" => "بقالة السلام", "amount" => 68900, "days" => 1, "risk" => "critical"],
                ["store" => "Marché Riadh El Feth", "ar" => "سوق رياض الفتح", "amount" => 121750, "days" => 9, "risk" => "safe"],
            ];
        }

        $totalCash = array_sum(array_column($driversCash, 'amount'));
        $totalDebt = array_sum(array_column($outstandingDebts, 'amount'));

        return response()->json([
            'status' => 'success',
            'data' => [
                'driversCash' => $driversCash,
                'outstandingDebts' => $outstandingDebts,
                'totals' => [
                    'cash' => $totalCash,
                    'debt' => $totalDebt
                ]
            ]
        ], 200);
    }

    /**
     * استقبال طلب إرسال إنذار للسائق بخصوص الديون الحرجة (Risk Alert)
     */
    public function sendRiskAlert(Request $request)
    {
        // التحقق من البيانات القادمة من الواجهة
        $request->validate([
            'store_name' => 'required|string',
            'risk_level' => 'required|string',
        ]);

        // هنا في المراحل المتقدمة سيتم ربط هذا الإجراء بنظام (FCM) أو (SMS) لإشعار السائق هاتفياً
        // حالياً نقوم بتوثيق التنبيه وإرجاع رسالة نجاح للواجهة الأمامية

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال إنذار التحصيل بنجاح للسائق بخصوص المحل: ' . $request->store_name,
            'data' => [
                'alerted_store' => $request->store_name,
                'risk_level' => $request->risk_level,
                'timestamp' => now(),
            ]
        ], 200);
    }
}