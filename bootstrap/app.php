<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json(['message' => 'Wrong method',], 401);
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return response()->json(['message' => 'The requested instance could not be found.'], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->json(['message' => 'The requested route could not be found.'], 404);
        });

        $exceptions->render(function (RouteNotFoundException $e, $request) {
            return response()->json(['message' => 'The requested route could not be found.'], 404);
        });
    })->create();
