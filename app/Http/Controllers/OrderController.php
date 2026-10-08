<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * استقبال ومعالجة طابور الطلبيات والفواتير القادمة من الواجهة (syncEngine)
     * يدعم الشحنات بأسماء 'receipts' أو 'orders' ويعالج حماية المخزون والديون والتكرار
     */
    public function sync(Request $request): JsonResponse
    {
        // مرونة الاستقبال: قبول 'receipts' (المبعوثة من syncEngine) أو 'orders'
        $ordersData = $request->input('receipts', $request->input('orders', []));

        if (empty($ordersData)) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد فواتير أو طلبيات في طابور المزامنة'
            ], 400);
        }

        $syncedOrders = [];
        $failedOrders = [];
        $syncedIds = [];

        foreach ($ordersData as $orderTmp) {
            DB::beginTransaction();

            try {
                $receiptId = $orderTmp['id'] ?? $orderTmp['receipt_id'] ?? null;

                // 1. فحص التكرار (Idempotency Check) لمنع دبلجة الفواتير عند إعادة المحاولة
                if ($receiptId) {
                    $existingOrder = Order::where('reference', $receiptId)
                        ->orWhere('id', $receiptId)
                        ->first();

                    if ($existingOrder) {
                        DB::rollBack();
                        $syncedIds[] = $receiptId;
                        $syncedOrders[] = [
                            'id' => $receiptId,
                            'store_name' => 'فاتورة مكررة (تمت معالجتها سابقاً)',
                            'total' => $existingOrder->total_amount
                        ];
                        continue;
                    }
                }

                // 2. جلب المحل / الزبون بالاعتماد على معرفات متعددة (store_id أو customerId)
                $storeId = $orderTmp['store_id'] ?? $orderTmp['customerId'] ?? $orderTmp['customer_id'] ?? null;
                $store = null;

                if ($storeId) {
                    $store = Store::find($storeId);
                } elseif (!empty($orderTmp['storeName']) || !empty($orderTmp['customerName'])) {
                    $storeName = $orderTmp['storeName'] ?? $orderTmp['customerName'];
                    $store = Store::where('name', $storeName)->first();
                }

                if (!$store) {
                    $failedOrders[] = [
                        'id' => $receiptId,
                        'store_name' => $orderTmp['storeName'] ?? $orderTmp['customerName'] ?? 'غير معروف',
                        'reason' => 'المحل/الزبون غير مسجل في السيرفر الرئيسي'
                    ];
                    DB::rollBack();
                    continue;
                }

                // 3. احتساب القيمة الإجمالية
                $orderTotal = $orderTmp['total_amount'] ?? $orderTmp['totalAmount'] ?? $orderTmp['total'] ?? 0;

                // 4. فحص سقف الدين (مع مراعاة التجاوز الاستثنائي isOverridden)
                $isOverridden = $orderTmp['isOverridden'] ?? false;
                if (!$isOverridden && ($store->debt + $orderTotal) > $store->credit_limit) {
                    $failedOrders[] = [
                        'id' => $receiptId,
                        'store_name' => $store->name,
                        'reason' => 'فشلت المزامنة بسبب تجاوز سقف الدين المسموح به (Plafond)'
                    ];
                    DB::rollBack();
                    continue;
                }

                // 5. إنشاء الطلبية الرسمية وتسجيل التجاوز والاستثناءات إن وجدت
                $order = Order::create([
                    'reference' => $receiptId,
                    'store_id' => $store->id,
                    'total_amount' => $orderTotal,
                    'status' => 'completed',
                    'is_overridden' => $isOverridden,
                    'supervisor_pin' => $orderTmp['supervisorPin'] ?? null,
                ]);

                // 6. تحديث دين الزبون فوراً
                $store->debt += $orderTotal;
                $store->save();

                // 7. تفكيك عناصر الفاتورة وتحديث المخزون
                $items = $orderTmp['items'] ?? $orderTmp['receipt_items'] ?? [];
                $hasValidItems = false;

                foreach ($items as $item) {
                    $productId = $item['product_id'] ?? $item['productId'] ?? null;
                    $product = null;

                    if ($productId) {
                        $product = Product::find($productId);
                    } elseif (!empty($item['sku'])) {
                        $product = Product::where('sku', $item['sku'])->first();
                    }

                    if ($product) {
                        $qty = $item['qty'] ?? $item['quantity'] ?? 0;

                        if ($product->stock < $qty) {
                            throw new \Exception("الكمية المطلوبة غير متوفرة في المستودع للمنتج: " . $product->name);
                        }

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $qty,
                            'price' => $item['price'] ?? $item['unitPrice'] ?? $product->price,
                        ]);

                        $product->stock -= $qty;
                        $product->save();
                        $hasValidItems = true;
                    }
                }

                if (!$hasValidItems && !empty($items)) {
                    throw new \Exception("لا توجد عناصر صالحة أو المخزون فارغ للمنتجات المحددة.");
                }

                DB::commit();

                if ($receiptId) {
                    $syncedIds[] = $receiptId;
                }

                $syncedOrders[] = [
                    'id' => $receiptId ?? $order->id,
                    'store_name' => $store->name,
                    'total' => $order->total_amount
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('فشل مزامنة الفاتورة: ' . $e->getMessage());

                $failedOrders[] = [
                    'id' => $receiptId ?? null,
                    'store_name' => $store->name ?? ($orderTmp['storeName'] ?? 'غير معروف'),
                    'reason' => 'خطأ في المعالجة: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => count($syncedOrders) > 0,
            'message' => 'اكتملت عملية معالجة المزامنة المحاسبية والجرد للمستودع',
            'synced_count' => count($syncedOrders),
            'failed_count' => count($failedOrders),
            'synced_ids' => $syncedIds,
            'report' => [
                'synced' => $syncedOrders,
                'failed' => $failedOrders
            ]
        ], count($syncedOrders) > 0 ? 200 : 400);
    }
}