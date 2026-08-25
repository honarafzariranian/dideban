<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuditLog
{
    /**
     * Handle an incoming request.
     * Log all write operations for audit trail
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only log write operations
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }

        $response = $next($request);

        // Log the operation
        $this->logOperation($request, $response);

        return $response;
    }

    /**
     * Log the operation
     */
    protected function logOperation(Request $request, Response $response): void
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return;
            }

            $data = [
                'organization_id' => $user->organization_id,
                'user_id' => $user->id,
                'method' => $request->method(),
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->sanitizeRequestData($request->all()),
                'response_code' => $response->getStatusCode(),
                'created_at' => now(),
            ];

            // Store in database
            DB::table('audit_logs')->insert($data);
        } catch (\Exception $e) {
            // Don't let audit logging break the application
            report($e);
        }
    }

    /**
     * Sanitize request data for logging
     */
    protected function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeRequestData($value);
            }
        }

        return $data;
    }
}
