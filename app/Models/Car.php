<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'user_id',
        'make',
        'model',
        'license_plate',
        'seats',
        'color',
        'year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
