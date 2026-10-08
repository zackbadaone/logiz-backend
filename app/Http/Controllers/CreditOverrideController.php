<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CreditOverrideController extends Controller
{
    /**
     * 1. جلب الطلبات المعلقة للإدارة (برج المراقبة)
     */
    public function getPendingRequests(): JsonResponse
    {
        // نستخدم الـ Cache كذاكرة حية مؤقتة لطلبات التجاوز لتفادي المساس بجداول قاعدة البيانات
        $requests = Cache::get('pending_overrides', []);
        return response()->json($requests, 200);
    }

    /**
     * 2. السائق يرسل طلب تجاوز من الميدان (الفخ الميداني)
     */
    public function requestOverride(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver_name' => 'required|string',
            'store_name' => 'required|string',
            'requested_amount' => 'required|numeric'
        ]);

        $newRequest = [
            'id' => 'REQ-' . rand(1000, 9999),
            'driver' => $validated['driver_name'],
            'store' => $validated['store_name'],
            'requested' => $validated['requested_amount'],
            'status' => 'pending',
            'timestamp' => now()->toDateTimeString()
        ];

        // جلب الطابور الحالي، إضافة الطلب الجديد، وحفظه لمدة 24 ساعة فقط
        $requests = Cache::get('pending_overrides', []);
        array_push($requests, $newRequest);
        Cache::put('pending_overrides', $requests, now()->addHours(24));

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب التجاوز إلى برج المراقبة بنجاح',
            'data' => $newRequest
        ], 201);
    }

    /**
     * 3. المدير يوافق ويصدر التصريح الرئاسي (التوكين المشفر)
     */
    public function approveOverride(Request $request, $id): JsonResponse
    {
        $requests = Cache::get('pending_overrides', []);
        $updated = false;
        $issuedToken = null;

        foreach ($requests as &$req) {
            if ($req['id'] === $id) {
                $req['status'] = 'approved';
                // توليد توكين مؤقت محمي
                $issuedToken = 'AUTH-' . bin2hex(random_bytes(8));
                $req['token'] = $issuedToken;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            Cache::put('pending_overrides', $requests, now()->addHours(24));
            return response()->json([
                'success' => true, 
                'message' => 'تم إصدار تصريح التجاوز بنجاح',
                'temporary_token' => $issuedToken
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'طلب التجاوز غير موجود أو منتهي الصلاحية'], 404);
    }
}