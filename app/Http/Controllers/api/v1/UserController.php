<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\v1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function index(Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($request->user()->load('picture'))
            ],
        ], 200);
    }

    public function show() {
        //
    }

    public function update(Request $request) {
        //
    }

    public function delete() {
        //
    }
}
