<?php

namespace App\Models\Api\Wallet;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory; 

    protected $fillable = ['user_id', 'balance'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add funds to wallet
    public function addFunds($amount)
    {
        $this->balance += $amount;
        $this->save();
    }

    // Deduct funds from wallet
    public function deductFunds($amount)
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->save();
            return true;
        }
        return false;
    }
}
