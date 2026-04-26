<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds request correlation and structured completion logs for HTTP traffic.
 */
final readonly class ObserveHttpRequest
{
    /**
     * Attach request/trace IDs, then log a safe request summary after routing.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $requestId = $this->requestId($request);
        $traceId = $this->traceId($request, $requestId);

        $request->headers->set('X-Request-Id', $requestId);
        $request->headers->set('X-Trace-Id', $traceId);
        $request->attributes->set('request_id', $requestId);
        $request->attributes->set('trace_id', $traceId);

        Log::withContext([
            'request_id' => $requestId,
            'trace_id' => $traceId,
        ]);

        try {
            $response = $next($request);
            $response->headers->set('X-Request-Id', $requestId);
            $response->headers->set('X-Trace-Id', $traceId);

            $tenant = $request->attributes->get(TenantContext::class);
            $route = $request->route();

            Log::info('http_request_completed', [
                'tenant_id' => $tenant instanceof TenantContext ? $tenant->tenantId : null,
                'shop_id' => $tenant instanceof TenantContext ? $tenant->shopId : null,
                'route' => $route?->getName() ?? $route?->uri() ?? $request->path(),
                'method' => $request->method(),
                'path' => '/'.$request->path(),
                'status' => $response->getStatusCode(),
                'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            return $response;
        } finally {
            Log::withoutContext(['request_id', 'trace_id']);
        }
    }

    private function requestId(Request $request): string
    {
        $requestId = trim((string) $request->headers->get('X-Request-Id', ''));

        if ($requestId !== '') {
            return Str::limit($requestId, 128, '');
        }

        return (string) Str::uuid();
    }

    private function traceId(Request $request, string $fallback): string
    {
        $traceId = trim((string) $request->headers->get('X-Trace-Id', ''));

        if ($traceId !== '') {
            return Str::limit($traceId, 128, '');
        }

        $traceparent = (string) $request->headers->get('traceparent', '');

        if (preg_match('/^[\da-f]{2}-([\da-f]{32})-[\da-f]{16}-[\da-f]{2}$/i', $traceparent, $matches) === 1) {
            return strtolower($matches[1]);
        }

        return $fallback;
    }
}
