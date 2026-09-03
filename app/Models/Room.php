<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'type',
        'price_per_night',
        'capacity',
        'description',
        'status',
    ];

    public function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
        ];
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
