<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportRosterRequest;
use App\Models\CheckInCheckOutEvent;
use App\Models\Event;
use App\Models\FlightEvent;
use App\Models\Roster;
use DateTime;
use DOMXPath;
use Illuminate\Http\Request;
use DOMDocument;
use Illuminate\Support\Facades\DB;
class ImportController extends Controller
{
    public function importRoster(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'roster' => 'required|file|mimes:html',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "errors" => $validator->errors()]);
        }
        $file = $request->file('roster');
        $filePath = $file->store('rosters'); // Saves in storage/app/rosters

        // Get full path
        $fullPath = storage_path("app/{$filePath}");

        // Read the stored file
        $content = file_get_contents($fullPath);
        // $content = file_get_contents($file->getRealPath());

        // dd($fullPath);
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $data = $dom->loadHTML($content);
        $xpath = new DOMXPath($dom);

        $matrix = $xpath->query("//*[contains(@id, 'ctl00_Main_activityGrid_')]");
        $roster = $xpath->query("//*[contains(@id, 'row printOnly')]");
        $parserMatrix = [];
        $activeDateIndex = '';
        $selectedValue = $xpath->evaluate("string(//select[contains(@id, 'ctl00_Main_periodSelect')]//option[@selected]/@value)");
        $datePair = explode('|', $selectedValue);
        $start_date = $datePair[0];
        $end_date = $datePair[1];
        $startMonth = date('m', strtotime($start_date));
        $startYear = date('Y', strtotime($start_date));

        foreach ($matrix as $key => $rows) {
            if ($key > 0) {// dd();
                $buffer = [];
                $rows = $rows->getElementsByTagName('td');
                foreach ($rows as $cell) {
                    // dd($cell->getAttribute('class'));
                    if ($cell->getAttribute('class') == 'lineLeft activitytablerow-date') {
                        if ($cell->nodeValue != '' && $key > 0) {
                            $activeDateIndex = $this->getFormattedDateIndex($cell->nodeValue, $startYear, $startMonth, $activeDateIndex);
                        }
                    }
                    if (
                        str_contains($cell->getAttribute('class'), 'lineLeft lineleft')
                        || str_contains($cell->getAttribute('class'), 'checkinlt') || str_contains($cell->getAttribute('class'), 'checkoutlt')
                        || str_contains($cell->getAttribute('class'), 'stdlt') || str_contains($cell->getAttribute('class'), 'stalt')
                        || str_contains($cell->getAttribute('class'), 'visible-sm-custom') || str_contains($cell->getAttribute('class'), 'visible-none-custom') || str_contains($cell->getAttribute('class'), 'lineLeft dontPrint expand-icon')
                    )
                        continue;
                    $buffer[str_replace('activitytablerow-', '', $cell->getAttribute('class'))] = html_entity_decode(trim($cell->nodeValue));
                    if (str_contains($cell->getAttribute('class'), 'Tailnumber'))
                        break;
                }
                $parserMatrix[$activeDateIndex][] = $buffer;
            }
        }
        $response = $this->insertParsedDataToDatabase($parserMatrix, $filePath, $start_date, $activeDateIndex);
        if ($response['status'] == true)
            return response()->json(['status' => $response['status'], 'message' => 'Roster imported successfully']);
        else
            return response()->json($response);

    }

    private function getFormattedDateIndex($dateText, &$startYear, &$startMonth, &$previousDay)
    // This logic detects when the month or year changes in the roster data. It assumes that the gap between consecutive days in the table is usually more than 26 days.
    // If the difference between the current day and the previous day is greater than 26, we assume the month has changed.
    // However, there may be other scenarios where this logic might not work correctly. 

    {
        $buffer = explode(' ', trim($dateText));
        $day = (int) $buffer[1];

        if ($previousDay !== null && $day < date('d', strtotime($previousDay))) {
            $startMonth++;
            if ($startMonth > 12) {
                $startMonth = 1;
                $startYear++;
            }
        }
        return sprintf("%04d-%02d-%02d", $startYear, $startMonth, $day);
    }
    private function insertParsedDataToDatabase($parserMatrix, $filePath, $start_date, $end_date)
    {
        // dd($parserMatrix, $fullPath,$start_date,$end_date);
        // DB::transaction(function () {
        // dd('hllo');
        DB::beginTransaction();
        try {
            $roster = Roster::create([
                'source_file' => $filePath,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($parserMatrix as $key => $activeDateIndex) {
                // dd($activeDateIndex);
                foreach ($activeDateIndex as $event) {

                    if (isset($event['activity']) && $event['activity'] && $event['activity'] != '\u{A0}' && $event['activityRemark'] && $event['activityRemark'] && $event['activityRemark'] != '\u{A0}') {
                        if (preg_match("/^DX\s*0*(\d+)$/", $event['activity']) && preg_match("/^DX\s*0*(\d+)$/", $event['activity'])) {

                            if (isset($event['checkinutc']) && trim($event['checkinutc']) !== '' && !ctype_space($event['checkinutc']) && $event['checkinutc'] !== "\u{A0}") {  //chcekin event log 
                                $eventLogCIN = Event::create([
                                    'roster_id' => $roster->id,
                                    'event_type' => Event::CHECK_IN,
                                    'start_time' => $this->dateformater($key, $event['checkinutc']),
                                    'end_time' => $this->dateformater($key, $event['stdutc']),
                                    'location' => $event['fromstn'],
                                ]);

                            }
                            $eventLogFE = Event::create([
                                'roster_id' => $roster->id,
                                'event_type' => Event::FLIGHT,
                                'start_time' => $this->dateformater($key, $event['stdutc']),
                                'end_time' => $this->dateformater($key, $event['stautc']),
                                'location' => $event['fromstn'],
                            ]);
                            $flightEvent = FlightEvent::create([
                                'event_id' => $eventLogFE->id,
                                'flight_number' => $event['activity'],
                                'departure_airport' => $event['fromstn'],
                                'arrival_airport' => $event['tostn'],
                                'std' => $this->dateformater($key, $event['stdutc']),
                                'sta' => $this->dateformater($key, $event['stautc']),
                                'aircraft_reg' => $event['Tailnumber'],
                            ]);
                            if (isset($eventLogCIN)) {
                                $checkInEvent = CheckInCheckOutEvent::create([
                                    'event_id' => $eventLogCIN->id,
                                    'linked_flight_id' => $flightEvent->id,
                                    'airport_code' => $event['fromstn'],
                                ]);
                            }
                            if (isset($event['checkoututc']) && trim($event['checkoututc']) !== '' && !ctype_space($event['checkoututc']) && $event['checkoututc'] !== "\u{A0}") {//chcekout event log
                                $eventLogCOUT = Event::create([
                                    'roster_id' => $roster->id,
                                    'event_type' => Event::CHECK_OUT,
                                    'start_time' => $this->dateformater($key, $event['checkoututc']),
                                    'end_time' => $this->dateformater($key, $event['stdutc']),
                                    'location' => $event['tostn'],
                                ]);
                                $checkInEvent = CheckInCheckOutEvent::create([
                                    'event_id' => $eventLogCOUT->id,
                                    'linked_flight_id' => $flightEvent->id,
                                    'airport_code' => $event['tostn'],
                                ]);
                            }
                        } elseif ($event['activity'] == "OFF") {
                            $eventDayoff = Event::create([
                                'roster_id' => $roster->id,
                                'event_type' => Event::DAY_OFF,
                                'start_time' => $this->dateformater($key, $event['stdutc']),
                                'end_time' => $this->dateformater($key, $event['stautc']),
                                'location' => $event['fromstn'],
                            ]);
                        } elseif ($event['activity'] == "SBY") {


                            if (isset($event['checkinutc']) && trim($event['checkinutc']) !== '' && !ctype_space($event['checkinutc']) && $event['checkinutc'] !== "\u{A0}") {  //chcekin event log 
                                // $counter++;
                                // if($counter==2)
                                //     dd($event['checkinutc']);
                                $eventLogCIN = Event::create([
                                    'roster_id' => $roster->id,
                                    'event_type' => Event::CHECK_IN,
                                    'start_time' => $this->dateformater($key, $event['checkinutc']),
                                    'end_time' => $this->dateformater($key, $event['stdutc']),
                                    'location' => $event['fromstn'],
                                ]);
                                $checkInEvent = CheckInCheckOutEvent::create([
                                    'event_id' => $eventLogCIN->id,
                                    'airport_code' => $event['fromstn'],
                                ]);
                            }
                            $eventStandby = Event::create([
                                'roster_id' => $roster->id,
                                'event_type' => Event::STAND_BY,
                                'start_time' => $this->dateformater($key, $event['stdutc']),
                                'end_time' => $this->dateformater($key, $event['stautc']),
                                'location' => $event['fromstn'],
                            ]);
                            if (isset($event['checkoututc']) && trim($event['checkoututc']) !== '' && !ctype_space($event['checkoututc']) && $event['checkoututc'] !== "\u{A0}") {//chcekout event log
                                $eventLogCOUT = Event::create([
                                    'roster_id' => $roster->id,
                                    'event_type' => Event::CHECK_OUT,
                                    'start_time' => $this->dateformater($key, $event['checkoututc']),
                                    'end_time' => $this->dateformater($key, $event['stdutc']),
                                    'location' => $event['tostn'],
                                ]);
                                $checkInEvent = CheckInCheckOutEvent::create([
                                    'event_id' => $eventLogCOUT->id,
                                    'airport_code' => $event['tostn'],
                                ]);
                            }
                        } else { //unknown Events
                            $eventUnknown = Event::create([
                                'roster_id' => $roster->id,
                                'event_type' => Event::UNKOWN,
                                'start_time' => $this->dateformater($key, $event['stdutc']),
                                'end_time' => $this->dateformater($key, $event['stautc']),
                                'location' => $event['fromstn'],
                            ]);
                        }
                    }

                }
            }
            DB::commit();
            return ["status" => true, "message" => "Data insertion successfull"];

        } catch (\Exception $e) {
            DB::rollBack();
            return ["status" => false, "message" => $e->getMessage(), 'file_name' => $e->getFile(), 'line_no' => $e->getLine(), 'code_snippet' => $e->getCode()];
        }
        // });
    }

    private function dateformater($key, $time)
    {
        $time = str_pad($time, 4, "0", STR_PAD_LEFT); //always 4 digits
        $hours = substr($time, 0, 2); // Extract hours
        $minutes = substr($time, 2, 2); // Extract minutes

        $dateTime = DateTime::createFromFormat('Y-m-d H:i', $key . " " . $hours . ":" . $minutes);

        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        return $formattedDateTime;
    }
}
