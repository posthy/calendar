<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    const RECURRENCE_NOREPEAT = 'no_repeat';
    const RECURRENCE_EVERYWEEK = 'every_week';
    const RECURRENCE_ODDWEEKS = 'odd_weeks';
    const RECURRENCE_EVENWEEKS = 'even_weeks';

    protected $fillable = [ 
        'client_name',
        'start_date',
        'end_date',
        'recurrence',
        'weekday',
        'daytime'
    ];
    
    public static function getRecurrences()
    {
        return [
            self::RECURRENCE_NOREPEAT,
            self::RECURRENCE_EVERYWEEK,
            self::RECURRENCE_ODDWEEKS,
            self::RECURRENCE_EVENWEEKS
        ];
    } 
}
