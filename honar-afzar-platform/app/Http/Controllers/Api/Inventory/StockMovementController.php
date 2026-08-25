<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\InventoryProduct;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements
     */
    public function index(Request $request): JsonResponse
    {
        $movements = StockMovement::where('organization_id', $request->user()->organization_id)
            ->with(['warehouse', 'product', 'creator', 'approver'])
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->warehouse_id, fn($q, $warehouseId) => $q->where('warehouse_id', $warehouseId))
            ->when($request->product_id, fn($q, $productId) => $q->where('product_id', $productId))
            ->when($request->date_from, fn($q, $date) => $q->where('created_at', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->where('created_at', '<=', $date))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }

    /**
     * Store a newly created stock movement
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:inventory_products,id',
            'type' => 'required|in:receipt,issue,transfer,adjustment,return',
            'quantity' => 'required|numeric',
            'unit_cost' => 'required|numeric|min:0',
            'batch_number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        $movement = DB::transaction(function () use ($validated, $request) {
            return StockMovement::create([
                'organization_id' => $request->user()->organization_id,
                'uuid' => Str::uuid(),
                ...$validated,
                'created_by' => $request->user()->id,
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'حرکت انبار با موفقیت ایجاد شد',
            'data' => $movement,
        ], 201);
    }

    /**
     * Display the specified stock movement
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $movement = StockMovement::where('organization_id', $request->user()->organization_id)
            ->with(['warehouse', 'product', 'creator', 'approver'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $movement,
        ]);
    }

    /**
     * Approve a stock movement
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $movement = StockMovement::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $movement->approve($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'حرکت انبار با موفقیت تأیید شد',
            'data' => $movement,
        ]);
    }

    /**
     * Cancel a stock movement
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $movement = StockMovement::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $movement->cancel($request->user()->id, $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'حرکت انبار با موفقیت لغو شد',
            'data' => $movement,
        ]);
    }

    /**
     * Get stock summary for a warehouse
     */
    public function warehouseStock(Request $request, int $warehouseId): JsonResponse
    {
        $stocks = Stock::where('organization_id', $request->user()->organization_id)
            ->where('warehouse_id', $warehouseId)
            ->with('product')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stocks' => $stocks,
                'summary' => [
                    'total_products' => $stocks->count(),
                    'total_quantity' => $stocks->sum('quantity'),
                    'total_value' => $stocks->sum(fn($s) => $s->quantity * $s->unit_cost),
                    'low_stock_count' => $stocks->filter(fn($s) => $s->isLowStock())->count(),
                    'expired_count' => $stocks->filter(fn($s) => $s->isExpired())->count(),
                ],
            ],
        ]);
    }

    /**
     * Get low stock alerts
     */
    public function lowStock(Request $request): JsonResponse
    {
        $lowStockProducts = InventoryProduct::where('organization_id', $request->user()->organization_id)
            ->whereHas('stocks', function ($q) {
                $q->havingRaw('SUM(quantity) <= products.reorder_point')
                  ->groupBy('product_id');
            })
            ->with(['stocks.warehouse', 'category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockProducts->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category->name ?? null,
                'reorder_point' => $product->reorder_point,
                'current_stock' => $product->getTotalStock(),
                'stocks' => $product->stocks->map(fn($stock) => [
                    'warehouse' => $stock->warehouse->name,
                    'quantity' => $stock->quantity,
                ]),
            ]),
        ]);
    }
}
