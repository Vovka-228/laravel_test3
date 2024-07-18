<?php

namespace App\Exceptions;

use Exception;


class Handler extends Exception
{
    protected $levels = [
        //
    ];
    protected $dontReport = [
        //
    ];
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->renderable(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                if ($exception instanceof AuthenticationException) {
                    return $request->expectsJson() ?:
                        response()->json([
                            'message' => 'Unauthenticated.',
                            'status' => 401,
                            'Description' => 'Missing or Invalid Access Token'
                        ], 401);
                }
            }
        });
    }
}
