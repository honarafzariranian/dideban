<?php
namespace App\Http\Controllers\Api\Correspondence;
use App\Http\Controllers\Controller;
use App\Models\Correspondence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorrespondenceController extends Controller {
    public function index(Request $request): JsonResponse {
        $letters = Correspondence::where('organization_id', $request->user()->organization_id)
            ->with(['fromDepartment', 'toDepartment', 'creator'])
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('date', 'desc')->paginate($request->per_page ?? 15);
        return response()->json(['success' => true, 'data' => $letters]);
    }

    public function store(Request $request): JsonResponse {
        $v = $request->validate([
            'type' => 'required|in:incoming,outgoing,internal', 'date' => 'required|date',
            'subject' => 'required|string|max:255', 'body' => 'nullable|string',
            'from_department_id' => 'nullable|exists:departments,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'priority' => 'in:low,normal,high,urgent', 'deadline' => 'nullable|date',
        ]);
        $letter = Correspondence::create([
            'organization_id' => $request->user()->organization_id,
            'created_by' => $request->user()->id, 'status' => 'draft', ...$v,
        ]);
        return response()->json(['success' => true, 'message' => 'نامه ایجاد شد', 'data' => $letter], 201);
    }

    public function show(Request $request, int $id): JsonResponse {
        $letter = Correspondence::where('organization_id', $request->user()->organization_id)
            ->with(['fromDepartment', 'toDepartment', 'creator', 'approver', 'attachments'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $letter]);
    }

    public function approve(Request $request, int $id): JsonResponse {
        $letter = Correspondence::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        $letter->approve($request->user()->id);
        return response()->json(['success' => true, 'message' => 'نامه تأیید شد', 'data' => $letter]);
    }

    public function destroy(Request $request, int $id): JsonResponse {
        $letter = Correspondence::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        if ($letter->status !== 'draft') return response()->json(['success' => false, 'message' => 'فقط پیش‌نویس قابل حذف است'], 400);
        $letter->delete();
        return response()->json(['success' => true, 'message' => 'نامه حذف شد']);
    }
}
