<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::where('organization_id', $request->user()->organization_id)
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => 
                $q->where(function ($query) use ($search) {
                    $query->where('invoice_number', 'like', "%{$search}%")
                          ->orWhere('reference_name', 'like', "%{$search}%");
                })
            )
            ->orderBy('invoice_date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:sales,purchase',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'reference_name' => 'required|string|max:255',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice = DB::transaction(function () use ($validated, $request) {
            $invoice = Invoice::create([
                'organization_id' => $request->user()->organization_id,
                'type' => $validated['type'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'reference_name' => $validated['reference_name'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'created_by' => $request->user()->id,
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_percent' => $itemData['tax_percent'] ?? 0,
                ]);
                
                $item->total = $item->quantity * $item->unit_price;
                $item->save();
            }

            $invoice->calculateTotals();

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت ایجاد شد',
            'data' => $invoice->load('items'),
        ], 201);
    }

    /**
     * Display the specified invoice
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::where('organization_id', $request->user()->organization_id)
            ->with(['items', 'payments', 'creator', 'approver'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Approve invoice
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        if ($invoice->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'فقط فاکتورهای در انتظار قابل تأیید هستند',
            ], 400);
        }

        $invoice->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت تأیید شد',
            'data' => $invoice,
        ]);
    }

    /**
     * Get invoice statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $salesStats = Invoice::where('organization_id', $organizationId)
            ->where('type', 'sales')
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
            ->groupBy('status')
            ->get();

        $purchaseStats = Invoice::where('organization_id', $organizationId)
            ->where('type', 'purchase')
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $salesStats,
                'purchases' => $purchaseStats,
                'overdue_invoices' => Invoice::where('organization_id', $organizationId)
                    ->overdue()
                    ->count(),
            ],
        ]);
    }
}
