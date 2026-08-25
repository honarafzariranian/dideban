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
    /**
     * Handle user login with rate limiting
     */
    public function login(Request $request): JsonResponse
    {
        // Rate limiting for login attempts
        $rateLimitKey = 'login:' . ($request->email ?? $request->ip());
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            
            throw ValidationException::withMessages([
                'email' => ["تعداد تلاش‌های ورود بیش از حد مجاز است. لطفاً {$retryAfter} ثانیه صبر کنید."],
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($rateLimitKey, 900); // 15 minutes
            
            throw ValidationException::withMessages([
                'email' => ['ایمیل یا رمز عبور صحیح نیست.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['حساب کاربری شما غیرفعال شده است.'],
            ]);
        }

        // Clear rate limit on successful login
        RateLimiter::clear($rateLimitKey);

        // Record login
        $user->recordLogin($request->ip());

        // Revoke previous tokens (optional - for single session)
        // $user->tokens()->delete();

        // Create new token
        $token = $user->createToken($request->device_name, ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'ورود موفقیت‌آمیز بود',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar_url,
                    'organization' => $user->organization ? [
                        'id' => $user->organization->id,
                        'name' => $user->organization->name,
                        'slug' => $user->organization->slug,
                    ] : null,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->name,
                    ] : null,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Handle user registration
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',      // At least one uppercase
                'regex:/[a-z]/',      // At least one lowercase
                'regex:/[0-9]/',      // At least one number
                'regex:/[@$!%*#?&]/', // At least one special character
            ],
            'phone' => 'nullable|string|max:20',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'organization_id' => $request->organization_id,
            'is_active' => true,
        ]);

        // Assign default role
        $user->assignRole('user');

        // Create token
        $token = $user->createToken($request->device_name ?? 'web')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'ثبت‌نام موفقیت‌آمیز بود',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'organization' => $user->organization ? [
                        'id' => $user->organization->id,
                        'name' => $user->organization->name,
                        'slug' => $user->organization->slug,
                    ] : null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Handle user logout - revoke current token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'خروج موفقیت‌آمیز بود',
        ]);
    }

    /**
     * Handle user logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'از تمام دستگاه‌ها خارج شدید',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar_url,
                    'organization' => $user->organization ? [
                        'id' => $user->organization->id,
                        'name' => $user->organization->name,
                        'slug' => $user->organization->slug,
                    ] : null,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->name,
                    ] : null,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'products' => $user->organization ? $user->organization->active_products->map(fn($product) => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'persian_name' => $product->persian_name,
                        'slug' => $product->slug,
                        'icon' => $product->icon,
                        'color' => $product->color,
                    ]) : [],
                ],
            ],
        ]);
    }

    /**
     * Refresh authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        $newToken = $request->user()->createToken($token->name)->plainTextToken;
        $token->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $newToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Change password with current password verification
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password'
