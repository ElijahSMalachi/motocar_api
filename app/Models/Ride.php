<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    protected $fillable = [
        'pickup_location_id',
        'destination_location_id',
        'driver_id',
        'price_per_seat',
        'start_off_date',
        'return_date',
    ];
    
    protected $casts = [
        'seats' => 'integer',
        'available_seats' => 'integer',
    ];
    public function pickupLocation()
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
