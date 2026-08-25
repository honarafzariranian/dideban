<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\FiscalYear;
use App\Models\ChartOfAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $entries = JournalEntry::where('organization_id', $request->user()->organization_id)
            ->with(['fiscalYear', 'accountingPeriod', 'creator', 'approver'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->date_from, fn($q, $date) => $q->where('entry_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->where('entry_date', '<=', $date))
            ->orderBy('entry_date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $entries]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'entry_date' => 'required|date',
            'type' => 'required|in:general,receipt,payment,journal,opening,closing',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $entry = DB::transaction(function () use ($validated, $request) {
            $entry = JournalEntry::create([
                'organization_id' => $request->user()->organization_id,
                'fiscal_year_id' => $validated['fiscal_year_id'],
                'accounting_period_id' => $validated['accounting_period_id'],
                'entry_date' => $validated['entry_date'],
                'type' => $validated['type'],
                'description' => $validated['description'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['lines'] as $lineData) {
                $entry->lines()->create([
                    'account_id' => $lineData['account_id'],
                    'debit' => $lineData['debit'],
                    'credit' => $lineData['credit'],
                    'description' => $lineData['description'] ?? null,
                ]);
            }

            $entry->calculateTotals();
            return $entry;
        });

        return response()->json([
            'success' => true,
            'message' => 'سند حسابداری با موفقیت ایجاد شد',
            'data' => $entry->load(['lines.account', 'fiscalYear', 'accountingPeriod']),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('organization_id', $request->user()->organization_id)
            ->with(['lines.account', 'fiscalYear', 'accountingPeriod', 'creator', 'approver', 'poster'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $entry]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $entry->approve($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'سند با موفقیت تأیید شد',
            'data' => $entry,
        ]);
    }

    public function post(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $entry->post($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'سند با موفقیت ثبت شد',
            'data' => $entry,
        ]);
    }

    public function reverse(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);

        $entry = JournalEntry::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        $entry->reverse($request->user()->id, $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'سند با موفقیت برگشت خورد',
            'data' => $entry,
        ]);
    }

    public function trialBalance(Request $request): JsonResponse
    {
        $fiscalYear = FiscalYear::where('organization_id', $request->user()->organization_id)
            ->current()
            ->first();

        if (!$fiscalYear) {
            return response()->json(['success' => false, 'message' => 'سال مالی فعالی یافت نشد'], 404);
        }

        $accounts = ChartOfAccount::where('organization_id', $request->user()->organization_id)
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->get()
            ->map(fn($account) => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name_fa,
                'type' => $account->type,
                'balance' => $account->getBalance(),
            ])
            ->filter(fn($account) => $account['balance'] != 0);

        $totalDebit = $accounts->filter(fn($a) => in_array($a['type'], ['asset', 'expense']) && $a['balance'] > 0)->sum('balance');
        $totalCredit = $accounts->filter(fn($a) => in_array($a['type'], ['liability', 'equity', 'revenue']) && $a['balance'] > 0)->sum('balance');

        return response()->json([
            'success' => true,
            'data' => [
                'fiscal_year' => $fiscalYear,
                'accounts' => $accounts->values(),
                'summary' => [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
                ],
            ],
        ]);
    }
}
