<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Api\Wallet\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    
    public function addFunds(Request $request)
{
    $validated = $request->validate([
        'payment_method' => 'required|string', // Airtel Money, MTN Money, Bank
        'amount' => 'required|numeric|min:1'
    ]);

    $user = Auth::user();
    $wallet = $user->wallet;

    // Simulate API call to payment provider
    $paymentStatus = $this->simulatePaymentProvider($validated['payment_method'], $validated['amount']);

    if ($paymentStatus) {
        $wallet->addFunds($validated['amount']);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'transaction_type' => 'deposit',
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'status' => true
        ]);

        return response()->json(['message' => 'Funds added successfully']);
    }

    return response()->json(['message' => 'Payment failed'], 400);
}

// Simulate payment provider API call
private function simulatePaymentProvider($method, $amount)
{
    // Add real payment gateway integration here
    return true; // Simulating a successful payment
}

}
