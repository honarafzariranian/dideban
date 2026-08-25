<?php

namespace App\Http\Controllers\Api\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payrolls = Payroll::where('organization_id', $request->user()->organization_id)
            ->with(['creator', 'approver'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->year, fn($q, $y) => $q->where('year', $y))
            ->when($request->month, fn($q, $m) => $q->where('month', $m))
            ->orderBy('year', 'desc')->orderBy('month', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $payrolls]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255', 'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:1400|max:1500', 'pay_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $payroll = DB::transaction(function () use ($validated, $request) {
            $payroll = Payroll::create([
                'organization_id' => $request->user()->organization_id,
                'title' => $validated['title'], 'month' => $validated['month'],
                'year' => $validated['year'], 'pay_date' => $validated['pay_date'],
                'notes' => $validated['notes'] ?? null, 'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);

            $employees = Employee::where('organization_id', $request->user()->organization_id)
                ->active()->with('salaryStructure')->get();

            foreach ($employees as $emp) {
                $structure = $emp->salaryStructure;
                if ($structure) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id, 'employee_id' => $emp->id,
                        'base_salary' => $structure->base_salary,
                        'allowances' => $structure->allowances,
                    ]);
                }
            }

            $payroll->calculateTotals();
            return $payroll;
        });

        return response()->json([
            'success' => true, 'message' => 'لیست حقوق با موفقیت ایجاد شد',
            'data' => $payroll->load('items.employee'),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $payroll = Payroll::where('organization_id', $request->user()->organization_id)
            ->with(['items.employee', 'creator', 'approver'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $payroll]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $payroll = Payroll::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        $payroll->approve($request->user()->id);

        return response()->json(['success' => true, 'message' => 'لیست حقوق تأیید شد', 'data' => $payroll]);
    }
}
