<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckInCheckOutEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'linked_flight_id',
        'airport_code',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function linkedFlight()
    {
        return $this->belongsTo(FlightEvent::class, 'linked_flight_id');
    }
}

