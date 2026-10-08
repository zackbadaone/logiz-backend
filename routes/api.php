<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CreditOverrideController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\AnalyticsController; 
use App\Http\Controllers\StockController;
use App\Http\Controllers\CashReconciliationController; // 👈 استيراد متحكم الإغلاق المالي الجديد

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| هنا نفتح الأبواب الرسمية للنظام لجلب البيانات بصيغة JSON وإرسالها من الواجهة
| تم تحصينها بمستشعر التوكنات (Sanctum Middleware) لمنع التلاعب بجولات البيع.
|
*/

// 🔑 مسار الأمان المفتوح: يسمح للسائق بتسجيل الدخول لأول مرة واستلام التوكن
Route::post('/login', [AuthController::class, 'login']);

// 🛡️ جدار الحماية الصارم: كل المسارات أدناه تتطلب إرفاق Bearer Token صالح في الهيدر للعبور
Route::middleware('auth:sanctum')->group(function () {

    // 1. مسارات الحوانيت والزبائن (Stores)
    Route::get('/stores', [StoreController::class, 'index']);
    Route::post('/stores', [StoreController::class, 'store']); // استقبال زبون جديد من الميدان

    // 2. مسارات كتالوج المنتجات والمخزون (Products)
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']); // استقبال منتج جديد في الكتالوج

    // 3. محرك المزامنة الحقيقي واستقبال الطلبيات (Orders Sync)
    Route::post('/orders/sync', [OrderController::class, 'sync']);

    // 4. نظام التجاوز الديناميكي للسقف والتصاريح الرئاسية (Dynamic Credit Override)
    Route::get('/overrides/pending', [CreditOverrideController::class, 'getPendingRequests']); // جلب الطلبات المعلقة لبرج المراقبة
    Route::post('/overrides/request', [CreditOverrideController::class, 'requestOverride']); // إرسال طلب تجاوز من الميدان (السائق)
    Route::post('/overrides/approve/{id}', [CreditOverrideController::class, 'approveOverride']); // موافقة المدير وإصدار التوكين

    // 5. 📦 مسارات المخزن (Warehouse Dashboard)
    Route::get('/warehouse/loading-items', [WarehouseController::class, 'getLoadingItems']); // لجلب قائمة شحن الصباح
    Route::get('/warehouse/reconciliation/driver-scan', [WarehouseController::class, 'simulateDriverScan']); // لمسح هاتف السائق
    Route::post('/warehouse/validate-loading', [WarehouseController::class, 'validateLoading']); // لاعتماد شحنة الصباح
    Route::post('/warehouse/close-day', [WarehouseController::class, 'closeDay']); // لإغلاق اليوم والمطابقة

    // 6. 🚛 مسارات تتبع الأسطول اللحظي (Fleet Live Tracking)
    Route::get('/fleet/live', [FleetController::class, 'getLiveFleet']);

    // 7. 📊 مسارات الإحصاءات والتحليلات (Analytics & Risk Management)
    Route::get('/analytics/financial', [AnalyticsController::class, 'getFinancialSummary']); // جلب الملخص المالي
    Route::post('/analytics/alerts/send', [AnalyticsController::class, 'sendRiskAlert']); // إرسال إنذار الديون الحرجة

    // 8. 📦 مسارات إدارة حركات وتسويات المخزون (Inventory Management & Adjustments)
    Route::get('/stock/movements', [StockController::class, 'index']); // جلب سجل الحركات بالكامل
    Route::post('/stock/adjust', [StockController::class, 'adjust']); // تنفيذ تسوية مخزنية وتعديل الكمية

    // 9. 💰 مسارات الإغلاق المالي وتسوية العهدة (Financial Closure)
    Route::post('/reconciliation/close-day', [CashReconciliationController::class, 'store']); // 👈 المسار الجديد لاعتماد الكاش

    // 🚪 مسار الخروج الآمن وتدمير التوكن الميداني
    Route::post('/logout', [AuthController::class, 'logout']);
});