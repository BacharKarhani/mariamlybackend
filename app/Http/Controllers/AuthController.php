<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;

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

    /**
     * Demote admin back to normal user
     */
    public function removeAdmin(Request $request, $userId)
    {
        if ($request->user()->role_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can remove admin role.'
            ], 403);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->role_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'User is not an admin.'
            ], 400);
        }

        // 2 is the default normal user role_id used on register
        $user->role_id = 2;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Admin role removed successfully. User is now a normal user.',
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
     * Admin: view any user's profile by ID
     */
    public function viewUserProfile(Request $request, $userId)
    {
        if ($request->user()->role_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can view other users\' profiles.'
            ], 403);
        }

        $user = User::find($userId);

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

    /**
     * Send password reset verification code to user's email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        
        // Find the user to get their name
        $user = User::where('email', $email)->first();
        
        // Generate a 6-digit verification code
        $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store the verification code in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($verificationCode),
                'created_at' => now()
            ]
        );

        try {
            // Send email with verification code
            Mail::to($email)->send(new PasswordResetMail(
                $verificationCode,
                $user->fname . ' ' . $user->lname,
                $email
            ));

            return response()->json([
                'success' => true,
                'message' => 'Password reset verification code sent to your email.',
                'email' => $email,
                'expires_in_minutes' => 10
            ]);
        } catch (\Exception $e) {
            // Log the error (you might want to use Laravel's logging)
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again later.'
            ], 500);
        }
    }

    /**
     * Reset password using verification code
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = $request->email;
        $verificationCode = $request->verification_code;
        $password = $request->password;

        // Find the password reset record
        $passwordReset = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'No verification code found for this email. Please request a new one.'
            ], 400);
        }

        // Check if verification code is valid (not expired - 10 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 10) {
            // Delete expired verification code
            DB::table('password_resets')->where('email', $email)->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.'
            ], 400);
        }

        // Verify the verification code
        if (!Hash::check($verificationCode, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.'
            ], 400);
        }

        // Find the user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Update the user's password
        $user->password = Hash::make($password);
        $user->save();

        // Delete the used verification code
        DB::table('password_resets')->where('email', $email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.'
        ]);
    }

    /**
     * Verify the verification code without resetting password
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $verificationCode = $request->verification_code;

        // Find the password reset record
        $passwordReset = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'No verification code found for this email. Please request a new one.'
            ], 400);
        }

        // Check if verification code is valid (not expired - 10 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 10) {
            // Delete expired verification code
            DB::table('password_resets')->where('email', $email)->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.'
            ], 400);
        }

        // Verify the verification code
        if (!Hash::check($verificationCode, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification code is valid.',
            'email' => $email
        ]);
    }
}
