<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    
    public function index(Request $request)
    {
        $firstDay = new \DateTime($request->start);
        $lastDay = new \DateTime($request->end);
        
        $result = DB::table('calendars')->where('recurrence', 'no_repeat')
            ->whereBetween('start_date', [$firstDay->format('Y-m-d'), $lastDay->format('Y-m-d')])
            ->union(
                DB::table('calendars')->where('recurrence', '!=', 'no_repeat')
                    ->where('start_date','<', $lastDay)
/*                    ->where(function ($query) use ($lastDay) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $lastDay->format('Y-m-d'));
                }) */
            );
        $calendar = $result->get();

        $dates = [];
        foreach ($calendar as $record) 
        {
            $times = explode('-', $record->daytime);
            $start = new \DateTime($record->start_date);
            if ($start < $firstDay)
                $start = $firstDay;
            
            $end = $lastDay;
            if (!is_null($record->end_date) && $record->end_date < $lastDay)
                $end = new \DateTime($record->end_date);    
            
            $interval = new \DateInterval("P1D");
            $period = new \DatePeriod($start, $interval, $end);

            switch ($record->recurrence)            
            {
                case 'no_repeat':
                    $date = [
                        'title' => $record->client_name,
                        'start' => $record->start_date.'T'.$times[0],
                        'end' => $record->start_date.'T'.$times[1]
                    ];
                    $dates[] = $date;
                    break;
                case 'every_week':
                    foreach ($period as $nextDate)
                    {
                        $day = strtolower($nextDate->format("l"));
                        if ($day == $record->weekday)
                        {
                            $date = [
                                'groupId' => $record->id,
                                'title' => $record->client_name,
                                'start' => $nextDate->format('Y-m-d').'T'.$times[0],
                                'end' => $nextDate->format('Y-m-d').'T'.$times[1]
                            ];
                            $dates[] = $date;
                        }
                    }
                    break;
                case 'even_weeks':
                    foreach ($period as $nextDate)
                    {
                        $day = strtolower($nextDate->format("l"));
                        if ($day == $record->weekday)
                        {
                            $week = $nextDate->format("W");
                            if ($week %2 == 0)
                            {
                                $date = [
                                    'groupId' => $record->id,
                                    'title' => $record->client_name,
                                    'start' => $nextDate->format('Y-m-d').'T'.$times[0],
                                    'end' => $nextDate->format('Y-m-d').'T'.$times[1]
                                ];
                                $dates[] = $date;
                            }
                        }

                    }
                    break;
                case 'odd_weeks':
                    foreach ($period as $nextDate)
                    {
                        $day = strtolower($nextDate->format("l"));
                        if ($day == $record->weekday)
                        {
                            $week = $nextDate->format("W");
                            if ($week %2 == 1)
                            {
                                $date = [
                                    'groupId' => $record->id,
                                    'title' => $record->client_name,
                                    'start' => $nextDate->format('Y-m-d').'T'.$times[0],
                                    'end' => $nextDate->format('Y-m-d').'T'.$times[1]
                                ];
                                $dates[] = $date;
                            }
                        }
                    }
                    break;
                
            }
            
        }
        return $dates;
    }

    public function store(Request $request)
    {
        $valid = $this->validator($request);
        if ($valid === true)
        {
            $newEvent = [
                'client_name' => $request->client_name,
                'start_date' => $request->date,
                'recurrence' => $request->recurrence,
                'weekday' =>strtolower((new \DateTime($request->date))->format("l")),
                'daytime' => $request->start. '-'.$request->end
            ];
            if ($request->endDate)
            {
                $newEvent["end_date"] = $request->endDate;
            }
            
            $record = Calendar::create($newEvent);
            return response('Ok',200);
        }
        else
        {
            return $valid;
        }
    }

    protected function validator($request)
    {
            // var_dump($request->all()); exit;
        $validator = Validator::make($request->all(), [
            'client_name'=> 'required',
            'date' => 'required|date_format:Y-m-d',
            'start'=> 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
            'recurrence'=> ['required', Rule::in(Calendar::getRecurrences())],
            'endDate' => 'nullable|date_format:Y-m-d|after:date'
        ]);
        if ($validator->fails())
        {
            $errors = $validator->errors();
            return response(implode("<br/>\n", $errors->all()), status: 400);
        }

        $weekday = strtolower((new \DateTime($request->date))->format("l"));
        $start = $request->date;
        $daytime = $request->start. '-'.$request->end;
        $calendar = DB::table('calendars')
        ->where('weekday', $weekday)
        ->where('start_date', '<', $start)
        ->where(function ($query) use ($start) {
            $query->whereNull('end_date')
              ->orWhere('end_date', '>', $start);
        })
        ->where(function ($query) use ($daytime) {
            $query->whereRaw("CAST(SPLIT_PART(daytime, '-', 1) AS TIME) < CAST(SPLIT_PART(?, '-', 1) AS TIME)", [$daytime])
                ->orWhereRaw("CAST(SPLIT_PART(daytime, '-', 2) AS TIME) > CAST(SPLIT_PART(?, '-', 2) AS TIME)", [$daytime]);
        })
        ->get();
        if ($calendar->count())
        {
            if ($calendar->recurrence == 'every_week' || $request->recurrence == 'every_week' || $calendar->recurrence == $request->recurrence)
            {
                $errors = ["A vélasztott időpont ütközik egy már lefoglalt iővel"];
                return response(implode("<br/>\n", $errors), status: 400);
            }
        }

        return true;
    }
}
