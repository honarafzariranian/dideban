<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses
     */
    public function index(Request $request): JsonResponse
    {
        $warehouses = Warehouse::where('organization_id', $request->user()->organization_id)
            ->when($request->search, fn($q, $search) => 
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                })
            )
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $warehouses,
        ]);
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'manager_id' => 'nullable|exists:users,id',
            'is_main' => 'boolean',
        ]);

        $warehouse = Warehouse::create([
            'organization_id' => $request->user()->organization_id,
            'uuid' => Str::uuid(),
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'انبار با موفقیت ایجاد شد',
            'data' => $warehouse,
        ], 201);
    }

    /**
     * Display the specified warehouse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $warehouse = Warehouse::where('organization_id', $request->user()->organization_id)
            ->with(['stocks.product', 'manager'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'warehouse' => $warehouse,
                'summary' => $warehouse->getStockSummary(),
            ],
        ]);
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $warehouse = Warehouse::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'manager_id' => 'nullable|exists:users,id',
            'is_main' => 'boolean',
            'status' => 'in:active,inactive,maintenance',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'انبار با موفقیت بروزرسانی شد',
            'data' => $warehouse,
        ]);
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $warehouse = Warehouse::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        if ($warehouse->stocks()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'انبار دارای موجودی است و قابل حذف نیست',
            ], 400);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'انبار با موفقیت حذف شد',
        ]);
    }
}
