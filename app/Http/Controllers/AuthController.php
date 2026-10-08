<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * التحقق من بيانات السائق/المشرف وإصدار توكن أمني للدخول
     */
    public function login(Request $request): JsonResponse
    {
        // 1. التحقق من صحة المدخلات القادمة من الواجهة
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. البحث عن المستخدم في قاعدة البيانات
        $user = User::where('email', $request->email)->first();

        // 3. مطابقة كلمة المرور (قفل الأمان)
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البيانات المدخلة غير صحيحة، يرجى التأكد من الإيميل أو كلمة المرور.'],
            ]);
        }

        // 4. إصدار توكن أمني جديد (Sanctum) لربطه بجهاز السائق
        $token = $user->createToken('dinarflow-mobile-token')->plainTextToken;

        // 5. إرجاع استجابة ناجحة مع بيانات المستخدم والتوكن
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 200);
    }

    /**
     * تدمير الجلسة الحالية وإبطال التوكن الأمني
     */
    public function logout(Request $request): JsonResponse
    {
        // مسح التوكن الحالي لإنهاء الجلسة بأمان ومنع استخدامه مجدداً
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج وإقفال الجولة بأمان'
        ], 200);
    }
}