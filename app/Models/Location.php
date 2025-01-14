<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'country',
        'state',
        'street',
        'county',
    ];

    protected $casts = [
        'coordinates' => 'point',
    ];

    public function pickups()
    {
        return $this->hasMany(Ride::class, 'pickup_location_id');
    }

    public function destinations()
    {
        return $this->hasMany(Ride::class, 'destination_location_id');
    }
}
