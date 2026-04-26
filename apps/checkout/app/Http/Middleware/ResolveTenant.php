<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Application\Tenant\TenantResolver;
use App\Domain\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ResolveTenant
{
    public function __construct(private TenantResolver $tenants) {}

    /**
     * @param Closure(Request): Response $next
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
