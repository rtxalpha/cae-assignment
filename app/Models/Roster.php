<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_file',
        'start_date',
        'end_date',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
