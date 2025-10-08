<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ValidationHelper;
use App\Helpers\CookieHelper;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = ValidationHelper::register($request->all());
        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated->errors(),
            ], 422);
        }

        $data = $validated->validated();

        if ($user = User::where('email', $data['email'])
            ->orWhere('phone', $data['phone'])
            ->first()
        ) {
            $field = $user->email === $data['email'] ? 'Email' : 'Phone number';
            return response()->json([
                'message' => "$field already exists",
            ], 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('visitor');

        $token  = $user->createToken('auth_token')->plainTextToken;
        $cookie = CookieHelper::make($token);

        return response()->json([
            'message' => 'Registered successfully',
        ], 201)->withCookie($cookie);
    }

    public function login(Request $request)
    {
        $validator = ValidationHelper::login($request->all());
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token  = $user->createToken('auth_token')->plainTextToken;
        $cookie = CookieHelper::make($token);

        return response()->json([
            'message' => 'Logged in successfully',
            'data' => [
                'id' => $user->id,
                'role' => $user->role
            ]
        ], 200)->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $cookie = CookieHelper::forget();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200)->withCookie($cookie);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'instagram' => $user->instagram,
            'role' => $user->role,
            'avatar' => $user->avatar
        ], 200);
    }
}
