<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user->only([
                'id',
                'name',
                'email',
                'role',
            ]),
            'access_token' => $token,
            'token_type' => 'bearer',
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $user = User::where('email', $credentials['email'])->first();

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user->only([
                'id',
                'name',
                'email',
                'role',
            ]),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    // LOGOUT
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    // PROFIL PENGGUNA
    public function me()
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'user' => $user->only([
                'id',
                'name',
                'email',
                'role',
            ]),
        ]);
    }
}
