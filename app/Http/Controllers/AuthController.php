<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user and return access token
     */
    public function register(Request $request)
    {
        $request->validate([
            'fname' => 'required|string',
            'lname' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
            'gender' => 'required|in:male,female,other',
            'birthdate' => 'required|date',
        ]);

        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'role_id' => 2, // Default role: user
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'User registered successfully',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ], 201);
    }

    /**
     * Login user and return access token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Admin: Search user by first and last name
     */
    public function searchUserByName(Request $request)
    {
        $request->validate([
            'fname' => 'required|string',
            'lname' => 'required|string',
        ]);

        $user = User::where('fname', 'like', '%' . $request->query('fname') . '%')
            ->where('lname', 'like', '%' . $request->query('lname') . '%')
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user'    => $user
        ]);
    }

    /**
     * Promote user to admin
     */
    public function promoteToAdmin(Request $request, $userId)
    {
        // Change this line from !== to !=
        if ($request->user()->role_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can promote users.'
            ], 403);
        }
    
        $user = User::find($userId);
    
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
    
        $user->role_id = 1;
        $user->save();
    
        return response()->json([
            'success' => true,
            'message' => 'User promoted to admin successfully.',
            'user'    => $user
        ]);
    }

    public function ping()
    {
        return response()->json(['message' => 'pong']);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user()
        ]);
    }

    /**
     * Change logged-in user's password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password'             => 'required|string',
            'new_password'             => 'required|string|min:6',
            'new_password_confirmation'=> 'required|string|min:6',
        ]);

        $user = $request->user();

        if ($request->new_password !== $request->new_password_confirmation) {
            return response()->json([
                'success' => false,
                'message' => 'New password and confirmation do not match.'
            ], 422);
        }

        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password is incorrect.'
            ], 403);
        }

        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'New password cannot be the same as the old password.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.'
        ]);
    }
    public function totalUsers(Request $request)
{
    // extra safety: group already has is_admin, but we keep this guard
    if ($request->user()->role_id != 1) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    return response()->json(['total' => User::count()], 200);
}
}
