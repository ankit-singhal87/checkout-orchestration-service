<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Domain\Tenant\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Builds Problem Details responses for JSON clients hitting web checkout routes.
 */
final readonly class WebProblemDetailsResponse
{
    /**
     * Render a tenant-scoped Problem Details JSON response.
     *
     * @param  list<array{field: string, code: string, message: string}>  $errors
     */
    public function make(
        Request $request,
        TenantContext $tenant,
        string $type,
        string $title,
        int $status,
        string $detail,
        array $errors = [],
    ): JsonResponse {
        return response()
            ->json([
                'type' => 'https://checkout.example.test/problems/'.$type,
                'title' => $title,
                'status' => $status,
                'detail' => $detail,
                'instance' => '/'.$request->path(),
                'traceId' => (string) $request->headers->get('X-Trace-Id', $request->headers->get('X-Request-Id', '')),
                'tenant' => $tenant->tenantId,
                'shop' => $tenant->shopId,
                'errors' => $errors,
            ], $status)
            ->header('Content-Type', 'application/problem+json');
    }
}
