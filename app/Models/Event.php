<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    public const DAY_OFF = "DO";
    public const STAND_BY ="SBY" ;
    public const FLIGHT ="FLT" ;
    public const CHECK_IN ="CI" ; //handeled
    public const CHECK_OUT ="CO" ;//handeled
    public const UNKOWN ="UNK" ;

    protected $fillable = [
        'roster_id',
        'event_type',
        'start_time',
        'end_time',
        'location',
    ];

    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    public function flightEvent()
    {
        return $this->hasOne(FlightEvent::class);
    }

    public function checkinCheckoutEvent()
    {
        return $this->hasOne(CheckinCheckoutEvent::class);
    }
}

