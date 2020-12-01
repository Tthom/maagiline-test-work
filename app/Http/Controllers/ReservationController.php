<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
//Reservation Exceptions
use App\Exceptions\Reservation\StartTimeNotFoundReservationException;
use App\Exceptions\Reservation\DurationNotFoundReservationException;

class ReservationController extends Controller
{
    //
    public function index() {
        return Reservation::all();
    }

    public function book(Request $request) {
	//data: start_time && duration_in_minutes
	$attributes = $request->all();
	//need to calculate end time
	
	//need to check if time is available
	//need masseur id
	if(!$request->has(['start_time'])) {
	    throw new StartTimeNotFoundReservationException;
        }
	if(!$request->has(['duration_in_minutes'])) {
            throw new DurationNotFoundReservationException;
	}

	$reservation = new Reservation;
	$reservation->validateAttributes($attributes);
	$reservation->checkIfWorkingHours($attributes);
//	$reservation->masseur_id = 1;
	$start_time = $reservation->getStartTime($attributes);
	$end_time = $reservation->getEndTime($attributes);
        $masseur_id = $reservation->getAvailableMasseur($attributes)->id;

	$reservation->session_start_time = $start_time;
	$reservation->session_end_time = $end_time;
        $reservation->masseur_id = $masseur_id;
	$reservation->save();

        return response()->json($reservation, 201);
    }
}
