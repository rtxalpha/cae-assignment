<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'std',
        'sta',
        'aircraft_reg',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function checkinCheckoutEvents()
    {
        return $this->hasMany(CheckinCheckoutEvent::class, 'linked_flight_id');
    }
}

