<?php

use App\Domain\Tenant\TenantContext;
use App\Http\Middleware\ObserveHttpRequest;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ObserveHttpRequest::class);
        $middleware->alias([
            'tenant' => ResolveTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $tenant = $request->attributes->get(TenantContext::class);
            $errors = [];

            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'field' => (string) $field,
                        'code' => 'validation',
                        'message' => (string) $message,
                    ];
                }
            }

            $payload = [
                'type' => 'https://checkout.example.test/problems/validation-failed',
                'title' => 'Validation failed',
                'status' => 422,
                'detail' => 'Request fields failed validation.',
                'instance' => '/'.$request->path(),
                'traceId' => (string) $request->headers->get('X-Trace-Id', $request->headers->get('X-Request-Id', '')),
                'errors' => $errors,
            ];

            if ($tenant instanceof TenantContext) {
                $payload['tenant'] = $tenant->tenantId;
                $payload['shop'] = $tenant->shopId;
            }

            return response()
                ->json($payload, 422)
                ->header('Content-Type', 'application/problem+json');
        });
    })->create();
