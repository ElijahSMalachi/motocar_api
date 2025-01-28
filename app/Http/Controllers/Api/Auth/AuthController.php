<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {

            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'gender' => 'required|string|in:Male,Female',
                'dob' => 'required|date',
                'phone_number' => 'required|string|unique:users',
                'email' => 'sometimes|email',
                'password' => 'required|min:6|confirmed',
            ]);
            $phoneNumber = $request->phone_number;
            if (!str_starts_with($phoneNumber, '+260')) {
                $phoneNumber = '+260' . ltrim($phoneNumber, '0'); // Remove leading 0 if it exists
            }
    

            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->phone_number = $phoneNumber;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            $token = $user->createToken($request->first_name);

            // sendTwilioVerification($request->phone_number, 'sms');
            return response()->json(['msg' => 'user created successfully!', 'user' => $user, 'token' => $token->plainTextToken]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'msg' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // Log the error for debugging purposes
            Log::error('User registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'msg' => 'An error occurred while creating the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|min:6',
        ]);

        // Ensure the phone number starts with +260
        $phoneNumber = $request->phone_number;
        if (!str_starts_with($phoneNumber, '+260')) {
            $phoneNumber = '+260' . ltrim($phoneNumber, '0'); // Remove leading 0 if it exists
        }

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            abort(401, 'The provided phone number is not registered.');
        }

        if (!$user || !Hash::check($request->password, $user?->password)) {
            abort(401, 'The provided password is incorrect.');
        }

        $token = $user->createToken($user->first_name);
        return response()->json(['user' => $user, 'token' => $token?->plainTextToken]);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['msg' => 'You are logged out.']);
    }
}
