<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationIsSet
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user belongs to an organization
     * and set the organization context for the request.
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

        // Set organization context
        $request->attributes->set('organization_id', $user->organization_id);
        
        // You can also set this in a service or use a scope
        // For example, using a global scope or a service class
        
        return $next($request);
    }
}
