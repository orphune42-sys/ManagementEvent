<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            'phone_number' => 'required|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:100',
            'organization' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = DB::transaction(function () use ($request) {
            // Simpan akun
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            // Simpan profil peserta
            Participant::create([
                'user_id' => $user->id,
                'phone_number' => $request->phone_number,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'city' => $request->city,
                'occupation' => $request->occupation,
                'organization' => $request->organization,
            ]);

            return $user;
        });

        // Buat JWT
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user->load('participant'),
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

        $user = User::with('participant')
            ->where('email', $credentials['email'])
            ->first();

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
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
            'user' => $user->load('participant'),
        ]);
    }
}
