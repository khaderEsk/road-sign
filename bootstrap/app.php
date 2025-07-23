<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'customer.role' => \App\Http\Middleware\CustomerRoleCheck::class,
            'jwt.verify' => \App\Http\Middleware\JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException and $request->is('api/*')) {
                return response()->json([
                    'message' => "أنت غير مخوّل لتنفيذ هذا الإجراء",
                ], 403);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof ModelNotFoundException and $request->is('api/*')) {
                return response()->json([
                    'message' => "العنصر غير موجود",
                ]);
            }
        }, 401);
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof AuthenticationException and $request->is('api/*')) {
                return response()->json([
                    'message' => "غير مصرح لك بالوصول.",
                ], 401);
            }
        });
    })->create();
