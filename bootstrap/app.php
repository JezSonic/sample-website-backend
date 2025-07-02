<?php

use App\Exceptions\Auth\OAuth\AuthOAuthException;
use Firebase\JWT\ExpiredException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use App\Http\Middleware\TokenRefresh;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Nette\NotImplementedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', StartSession::class);
        $middleware->appendToGroup('api', ShareErrorsFromSession::class);
        $middleware->removeFromGroup('api', RedirectIfAuthenticated::class);
        $middleware->removeFromGroup('api', AddQueuedCookiesToResponse::class);
        $middleware->removeFromGroup('api', EnsureFrontendRequestsAreStateful::class);
        $middleware->appendToGroup('web', StartSession::class);
        $middleware->appendToGroup('web', ShareErrorsFromSession::class);
        $middleware->removeFromGroup('web', RedirectIfAuthenticated::class);
        $middleware->removeFromGroup('web', AddQueuedCookiesToResponse::class);
        $middleware->removeFromGroup('web', EnsureFrontendRequestsAreStateful::class);
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Register token refresh middleware
        $middleware->alias([
            'token.refresh' => TokenRefresh::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (AuthenticationException $e) {
            $code = 401;
            $responseBody = [
                'errors' => [
                    'type' => 'authentication_exception',
                    'message' => 'Not authenticated',
                    'code' => $code
                ]
            ];
            return response()->json($responseBody, $code);
        });

        $exceptions->renderable(function (AuthorizationException $e) {
            $code = 403;
            $responseBody = [
                'errors' => [
                    'type' => 'authorization_exception',
                    'message' => 'Forbidden',
                    'code' => $code
                ]
            ];
            return response()->json($responseBody, $code);
        });

        $exceptions->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'content' => [
                    'type' => 'not_found_http_exception',
                    'message' => 'Not found',
                    'code' => 404
                ]
            ], 404);
        });

        $exceptions->renderable(function (ValidationException $e) {
            $code = 400;
            $responseBody = [
                'type' => 'validation_exception',
                'errors' => $e->errors()
            ];
            return response()->json($responseBody, $code);
        });

        $exceptions->renderable(function (ExpiredException $e) {
            $code = 403;
            $responseBody = [
                'errors' => [
                    'type' => 'expired_exception',
                    'message' => 'Expired token',
                    'code' => $code
                ]
            ];
            return response()->json($responseBody, $code);

        });

        $exceptions->renderable(function (NotImplementedException $e) {
            return response()->json([
                'content' => [
                    'type' => 'not_implemented_exception',
                    'message' => 'Not implemented',
                    'code' => 501
                ]
            ], 501);
        });

        $exceptions->renderable(function (AuthOAuthException $e) {
            return response()->json([
                'content' => [
                    'type' => 'oauth_exception',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ], $e->getCode());
        });

        $exceptions->renderable(function (Throwable $e) {
            $response = [
                'message' => $e->getMessage(),
                'type' => 'generic_exception'
            ];
            if (config('app.debug')) {
                $response['debug'] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'type' => 'generic_exception'
                ];
            }

            return response()->json($response, 500);
        });
    })->create();
