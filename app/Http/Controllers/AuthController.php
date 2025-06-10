<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Неверный email или пароль'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email
            ]
        ]);
    }
}