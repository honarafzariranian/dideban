<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $rateLimitKey = 'login:' . ($request->email ?? $request->ip());
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages(['email' => ["تعداد تلاش‌ها بیش از حد مجاز است. لطفاً {$retryAfter} ثانیه صبر کنید."]]);
        }
        $request->validate(['email' => 'required|email', 'password' => 'required', 'device_name' => 'required|string|max:255']);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($rateLimitKey, 900);
            throw ValidationException::withMessages(['email' => ['ایمیل یا رمز عبور صحیح نیست.']]);
        }
        if (!$user->isActive()) {
            throw ValidationException::withMessages(['email' => ['حساب کاربری غیرفعال است.']]);
        }
        RateLimiter::clear($rateLimitKey);
        $user->recordLogin($request->ip());
        $token = $user->createToken($request->device_name, ['*'])->plainTextToken;
        return response()->json([
            'success' => true, 'message' => 'ورود موفقیت‌آمیز بود',
            'data' => ['user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email,
                'organization' => $user->organization ? ['id' => $user->organization->id, 'name' => $user->organization->name] : null,
                'roles' => $user->getRoleNames(), 'permissions' => $user->getAllPermissions()->pluck('name')],
                'token' => $token, 'token_type' => 'Bearer'],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'organization_id' => 'required|exists:organizations,id',
        ]);
        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'organization_id' => $request->organization_id, 'is_active' => true]);
        $user->assignRole('user');
        $token = $user->createToken($request->device_name ?? 'web')->plainTextToken;
        return response()->json(['success' => true, 'message' => 'ثبت‌نام موفقیت‌آمیز بود', 'data' => ['user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email], 'token' => $token]], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'خروج موفقیت‌آمیز بود']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'از تمام دستگاه‌ها خارج شدید']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json(['success' => true, 'data' => ['user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'organization' => $user->organization ? ['id' => $user->organization->id, 'name' => $user->organization->name] : null,
            'roles' => $user->getRoleNames(), 'permissions' => $user->getAllPermissions()->pluck('name')]]]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        $newToken = $request->user()->createToken($token->name)->plainTextToken;
        $token->delete();
        return response()->json(['success' => true, 'data' => ['token' => $newToken, 'token_type' => 'Bearer']]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['رمز عبور فعلی صحیح نیست.']]);
        }
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();
        return response()->json(['success' => true, 'message' => 'رمز عبور تغییر کرد. نشست‌های دیگر غیرفعال شدند.']);
    }

    public function sessions(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get();
        $currentTokenId = $request->user()->currentAccessToken()->id;
        $sessionData = $tokens->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'last_used_at' => $t->last_used_at?->toISOString(), 'created_at' => $t->created_at->toISOString(), 'is_current' => $t->id === $currentTokenId]);
        return response()->json(['success' => true, 'data' => ['sessions' => $sessionData]]);
    }

    public function revokeSession(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);
        if (!$token) return response()->json(['success' => false, 'message' => 'نشست یافت نشد'], 404);
        $token->delete();
        return response()->json(['success' => true, 'message' => 'نشست غیرفعال شد']);
    }
}
