<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredExeption;

class JWTMiddleware
{
    public function handle($request, Closure $next, $guard=null)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch (ExpiredExeption $e) {
            return response()->json(['error' => 'Token expired'], 400);
        } catch (Exception $e) {
            dd($e);
            return response()->json(['error' => 'Unknown error occurred']);
        }

        $user = User::find($credentials->sub);

        $request->auth = $user;

        return $next($request);
    }
}
