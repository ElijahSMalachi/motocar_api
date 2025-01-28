<?php

use App\Models\Api\Wallet\Transaction;
use Twilio\Rest\Client;

if (!function_exists('sendTwilioVerification')) {
    /**
     * Send a verification code via Twilio.
     *
     * @param string $phoneNumber
     * @param string $channel
     * @return string Verification SID
     */
    function sendTwilioVerification(string $phoneNumber, string $channel = 'sms'): string
    {
        $sid = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $serviceSid = env('TWILIO_VERIFY_SERVICE_SID');

        $twilio = new Client($sid, $token);

        $verification = $twilio->verify->v2->services($serviceSid)
            ->verifications
            ->create($phoneNumber, $channel);

        return $verification->sid;
    }
}

if (!function_exists('checkTwilioVerification')) {
    /**
     * Check a verification code via Twilio.
     *
     * @param string $phoneNumber
     * @param string $code
     * @return bool Verification status
     */
    function checkTwilioVerification(string $phoneNumber, string $code): bool
    {
        $sid = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $serviceSid = env('TWILIO_VERIFY_SERVICE_SID');

        $twilio = new Client($sid, $token);

        $verificationCheck = $twilio->verify->v2->services($serviceSid)
            ->verificationChecks
            ->create([
                'to' => $phoneNumber,
                'code' => $code,
            ]);

        return $verificationCheck->status === 'approved';
    }
}

function processDriverPayment($ride, $driver)
{
    $commissionRate = 0.12;
    $commission = $ride->total_cost * $commissionRate;

    $wallet = $driver->wallet;

    if ($wallet->deductFunds($commission)) {
        Transaction::create([
            'user_id' => $driver->id,
            'wallet_id' => $wallet->id,
            'transaction_type' => 'company_commission',
            'amount' => $commission,
            'status' => true
        ]);

        return true;
    }

    return false; // Insufficient balance
}

function processUserRidePayment($ride, $user)
{
    $transactionFee = $ride->transaction_fee;

    $wallet = $user->wallet;

    if ($wallet->deductFunds($transactionFee)) {
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'transaction_type' => 'transaction_fee',
            'amount' => $transactionFee,
            'status' => true
        ]);

        return true;
    }

    return false; // Insufficient balance
}

// Refund the fee if the ride is completed
function refundUserTransactionFee($ride, $user)
{
    $transactionFee = $ride->transaction_fee;
    $wallet = $user->wallet;

    $wallet->addFunds($transactionFee);

    Transaction::create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'transaction_type' => 'refund',
        'amount' => $transactionFee,
        'status' => true
    ]);
}

function storeFile($passed_file, $type)
{
    // Get the original file name and extension
    $image = $passed_file->getClientOriginalName();
    $fileName = pathinfo(str_replace(' ', '_', $image), PATHINFO_FILENAME);
    $extension = $passed_file->getClientOriginalExtension();
    
    // Create a unique file name
    $fileToStore = $fileName . '_' . time() . '.' . $extension;
    
    // Define the path structure
    $path = $type . '/' . date('Y') . '/' . date('M');
    
    // Store the file in the 'public' disk
    $storedPath = $passed_file->storeAs($path, $fileToStore, 'public');
    
    // Return the accessible URL
    return 'storage/' . $storedPath;
}

