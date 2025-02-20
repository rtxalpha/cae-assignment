<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\FlightEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function fetchEvents(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()]);
        }

        $events = Event::whereDate('start_time', '>=', $request->start_date)
            ->whereDate('start_time', '<=', $request->end_date)->orderBy('start_time','asc')
            ->paginate(25)->appends($request->query());

        return response()->json(["data" => $events, "status" => true]);
    }

    public function nextWeekFlightFromCurrentDate(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'current_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()]);
        }

        $currentDate = Carbon::parse($request->current_date);
        $nextWeek = (clone $currentDate)->addWeek();

        $flightEvents = FlightEvent::whereDate('std', '>=', $currentDate)
            ->whereDate('sta', '<=', $nextWeek)->orderBy('std','asc')
            ->paginate(10)->appends($request->query());

        return response()->json(["data" => $flightEvents, "status" => true]);
    }

    public function fetchStandbyEvents(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'current_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()]);
        }

        $currentDate = Carbon::parse($request->current_date);
        $nextWeek = (clone $currentDate)->addWeek();

        $flightEvents = Event::where('event_type', Event::STAND_BY)
            ->whereDate('start_time', '>=', $currentDate)
            ->whereDate('start_time', '<=', $nextWeek)->orderBy('start_time','asc')
            ->paginate(10)->appends($request->query());

        return response()->json(["data" => $flightEvents, "status" => true]);
    }

    public function fetchFlightsFromLocation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "location" => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()]);
        }

        $flights = FlightEvent::where('departure_airport', strtoupper($request->location))->paginate(10)->appends($request->query());
        return response()->json(["data" => $flights, "status" => true]);
    }
}
