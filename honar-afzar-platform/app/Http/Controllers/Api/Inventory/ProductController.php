<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $products = InventoryProduct::where('organization_id', $request->user()->organization_id)
            ->with(['category', 'unit'])
            ->when($request->search, fn($q, $search) => 
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%")
                          ->orWhere('barcode', 'like', "%{$search}%");
                })
            )
            ->when($request->category_id, fn($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($request->has('low_stock'), function ($q) {
                $q->whereHas('stocks', function ($sq) {
                    $sq->havingRaw('SUM(quantity) <= products.reorder_point')
                       ->groupBy('product_id');
                });
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_fa' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'has_serial_number' => 'boolean',
            'has_batch' => 'boolean',
            'has_expiry' => 'boolean',
        ]);

        $product = InventoryProduct::create([
            'organization_id' => $request->user()->organization_id,
            'uuid' => Str::uuid(),
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'محصول با موفقیت ایجاد شد',
            'data' => $product,
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $product = InventoryProduct::where('organization_id', $request->user()->organization_id)
            ->with(['category', 'unit', 'stocks.warehouse'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'total_stock' => $product->getTotalStock(),
                'total_value' => $product->getTotalValue(),
                'is_low_stock' => $product->isLowStock(),
            ],
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = InventoryProduct::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_fa' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'has_serial_number' => 'boolean',
            'has_batch' => 'boolean',
            'has_expiry' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'محصول با موفقیت بروزرسانی شد',
            'data' => $product,
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = InventoryProduct::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        if ($product->stocks()->where('quantity', '>', 0)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'محصول دارای موجودی است و قابل حذف نیست',
            ], 400);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'محصول با موفقیت حذف شد',
        ]);
    }

    /**
     * Get product stock summary
     */
    public function stockSummary(Request $request, int $id): JsonResponse
    {
        $product = InventoryProduct::where('organization_id', $request->user()->organization_id)
            ->with('stocks.warehouse')
            ->findOrFail($id);

        $stocks = $product->stocks()->with('warehouse')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product->only(['id', 'name', 'code', 'sku']),
                'total_stock' => $product->getTotalStock(),
                'total_value' => $product->getTotalValue(),
                'is_low_stock' => $product->isLowStock(),
                'stocks_by_warehouse' => $stocks->map(fn($stock) => [
                    'warehouse_id' => $stock->warehouse_id,
                    'warehouse_name' => $stock->warehouse->name,
                    'quantity' => $stock->quantity,
                    'reserved' => $stock->reserved_quantity,
                    'available' => $stock->available_quantity,
                    'unit_cost' => $stock->unit_cost,
                    'total_value' => $stock->getTotalValue(),
                ]),
            ],
        ]);
    }
}
