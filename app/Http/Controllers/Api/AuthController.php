<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:150',
            'email'    => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol'      => 'required|in:admin,usuario',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $usuario = Usuario::create($validated);

        $token = $usuario->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'usuario' => $usuario,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }

        $token = $usuario->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'usuario' => $usuario,
            'token'   => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Revoca SOLO el token actual (sesión actual)
        $request->user()->currentAccessToken()?->delete();

              return response()->json(['message' => 'Logout exitoso'], 200);
    }
}
