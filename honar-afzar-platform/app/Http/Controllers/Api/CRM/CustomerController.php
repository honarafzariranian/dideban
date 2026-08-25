<?php
namespace App\Http\Controllers\Api\CRM;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller {
    public function index(Request $request): JsonResponse {
        $customers = Customer::where('organization_id', $request->user()->organization_id)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%");
            }))
            ->orderBy('name')->paginate($request->per_page ?? 15);
        return response()->json(['success' => true, 'data' => $customers]);
    }

    public function store(Request $request): JsonResponse {
        $v = $request->validate(['name' => 'required|string|max:255', 'email' => 'nullable|email', 'phone' => 'nullable|string']);
        $customer = Customer::create(['organization_id' => $request->user()->organization_id, ...$v]);
        return response()->json(['success' => true, 'message' => 'مشتری ایجاد شد', 'data' => $customer], 201);
    }

    public function show(Request $request, int $id): JsonResponse {
        $customer = Customer::where('organization_id', $request->user()->organization_id)
            ->with(['opportunities', 'activities'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $customer]);
    }

    public function update(Request $request, int $id): JsonResponse {
        $customer = Customer::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        $v = $request->validate(['name' => 'required|string|max:255', 'email' => 'nullable|email']);
        $customer->update($v);
        return response()->json(['success' => true, 'message' => 'مشتری بروزرسانی شد', 'data' => $customer]);
    }

    public function destroy(Request $request, int $id): JsonResponse {
        $customer = Customer::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'مشتری حذف شد']);
    }

    public function stats(Request $request): JsonResponse {
        $orgId = $request->user()->organization_id;
        return response()->json(['success' => true, 'data' => [
            'total' => Customer::where('organization_id', $orgId)->count(),
            'leads' => Customer::where('organization_id', $orgId)->where('status', 'lead')->count(),
            'active' => Customer::where('organization_id', $orgId)->where('status', 'active')->count(),
        ]]);
    }
}
