<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            return response()->json([
                'user' => [
                    'name' => $user->name,
                    'surname' => $user->surname
                ],
                'token' => $user->createToken('api-token')->plainTextToken
            ]);
        }

        return response()->json(['message' => 'Неверный логин или пароль'], 401);
    }
}
