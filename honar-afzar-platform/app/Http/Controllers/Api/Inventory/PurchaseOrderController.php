<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::where('organization_id', $request->user()->organization_id)
            ->with(['supplier', 'warehouse', 'creator'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->supplier_id, fn($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when($request->search, fn($q, $search) => 
                $q->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                          ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
                })
            )
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $order = PurchaseOrder::create([
                'organization_id' => $request->user()->organization_id,
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'created_by' => $request->user()->id,
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_percent' => $itemData['discount_percent'] ?? 0,
                    'tax_percent' => $itemData['tax_percent'] ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);
                $item->calculateTotal();
                $item->save();
            }

            $order->calculateTotals();

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'سفارش خرید با موفقیت ایجاد شد',
            'data' => $order->load(['items.product', 'supplier', 'warehouse']),
        ], 201);
    }

    /**
     * Display the specified purchase order
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = PurchaseOrder::where('organization_id', $request->user()->organization_id)
            ->with(['items.product', 'supplier', 'warehouse', 'creator', 'approver'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Approve a purchase order
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $order = PurchaseOrder::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $order->approve($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'سفارش خرید با موفقیت تأیید شد',
            'data' => $order,
        ]);
    }

    /**
     * Receive a purchase order
     */
    public function receive(Request $request, int $id): JsonResponse
    {
        $order = PurchaseOrder::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($order, $validated) {
            foreach ($validated['items'] as $itemData) {
                $orderItem = $order->items()
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                if ($orderItem) {
                    $orderItem->received_quantity += $itemData['quantity'];
                    $orderItem->save();
                }
            }

            // Check if order is fully received
            $allReceived = $order->items->every(fn($item) => $item->is_fully_received);
            if ($allReceived) {
                $order->receive();
            } else {
                $order->update(['status' => 'partial']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'دریافت کالا با موفقیت ثبت شد',
            'data' => $order->load(['items.product']),
        ]);
    }

    /**
     * Cancel a purchase order
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = PurchaseOrder::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $order->cancel($validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'سفارش خرید با موفقیت لغو شد',
            'data' => $order,
        ]);
    }

    /**
     * Get purchase order statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $stats = PurchaseOrder::where('organization_id', $organizationId)
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => $stats,
                'total_orders' => PurchaseOrder::where('organization_id', $organizationId)->count(),
                'total_value' => PurchaseOrder::where('organization_id', $organizationId)->sum('total_amount'),
                'pending_approval' => PurchaseOrder::where('organization_id', $organizationId)
                    ->where('status', 'pending')
                    ->count(),
            ],
        ]);
    }
}
