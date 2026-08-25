<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Set the organization context for the request based on the authenticated user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر به هیچ سازمانی تعلق ندارد',
            ], 403);
        }

        // Set organization context in request
        $request->attributes->set('organization_id', $user->organization_id);

        return $next($request);
    }
}
