<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Application\Tenant\TenantResolver;
use App\Domain\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves and attaches tenant context before tenant-scoped routes run.
 */
final readonly class ResolveTenant
{
    /**
     * Create the tenant resolution middleware.
     */
    public function __construct(private TenantResolver $tenants) {}

    /**
     * Attach the resolved tenant to request attributes or fail closed.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenants->resolveHost($request->getHost());

        if (! $tenant instanceof TenantContext) {
            abort(404);
        }

        $request->attributes->set(TenantContext::class, $tenant);

        return $next($request);
    }
}
