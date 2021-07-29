<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    /**
     * Create a new user
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        try {
            $user = new User;
            $user->name = $request->json()->get('name');
            $user->email = $request->json()->get('email');
            $plainPassword = $request->json()->get('password');
            $user->password = app('hash')->make($plainPassword);

            $user->save();

            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);

        } catch(\Exception $e) {
            return response()->json(['message' => 'User registration failed'], 409);
        }
    }

    /**
     * Create JWT token
     *
     * @param User $user
     * @return string
     */
    protected function generateJwt(User $user)
    {
        $payload = [
            'iss' => 'lumen-jwt',
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60*60
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

    /**
     * Check user login and return JWT token
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->get('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist'], 400);
        }

        if (Hash::check($request->get('password'), $user->password)) {
            return response()->json(['token' => $this->generateJwt($user)], 200);
        }

        return response()->json(['error' => 'Incorrect email or password'], 400);
    }
}
