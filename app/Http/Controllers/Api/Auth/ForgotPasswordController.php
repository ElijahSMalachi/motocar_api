<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['phone_number' => 'required|string']);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            // Returning the response
            return response()->json(['msg' => 'User with that phone number doesn\'t exist!'], 404);
        } else {
            // Saving the token which will be used for checking on the frontend.
            // $resetCode = random_int(100000, 999999);

            // DB::table('password_reset_tokens')->updateOrInsert(
            //     ['phone_number' => $request->phone_number],
            //     ['token' => $resetCode, 'created_at' => now()]
            // );

            sendTwilioVerification('+260776584836', 'sms');

            return response()->json(['message' => 'Reset code sent successfully', 'phone_number' => '+260776584836']);
        }

        return response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
    
        $isVerified = checkTwilioVerification('+260776584836', $request->token);

        if (!$isVerified) {
            return response()->json(['message' => 'Invalid reset code'], 400);
        }

        $user = User::where('phone_number', '+'.$request->phone_number)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['message' => 'Password reset successfully']);
    }
}
