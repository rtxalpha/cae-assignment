<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('import-roster', [ImportController::class, 'importRoster']);    // Import roster from html file
Route::get('/events', [AnalyticsController::class,'fetchEvents']); // fetch events between dates 
Route::get('/flights/next-week', [AnalyticsController::class,'nextWeekFlightFromCurrentDate']); //fetch flights for the next week from a given date 
Route::get('/events/standby', [AnalyticsController::class,'fetchStandbyEvents']); //fetch standby events for the next week from a given date 
Route::get('/flights/from', [AnalyticsController::class,'fetchFlightsFromLocation']);