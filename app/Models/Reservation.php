<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//Reservation Exceptions
use App\Exceptions\Reservation\StartTimeNotFoundReservationException;
use App\Exceptions\Reservation\DurationNotFoundReservationException;
use App\Exceptions\Reservation\InvalidDateFormatReservationException;
use App\Exceptions\Reservation\DurationTooShortReservationException;
use App\Exceptions\Reservation\DurationTooLongReservationException;
use App\Exceptions\Reservation\InvalidDurationReservationException;
use App\Exceptions\Reservation\SessionNotAvailableReservationException;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = ['session_start_time', 'session_end_time', 'masseur_id'];
    protected $workingHours = [
        'day'   => ['min' => 1, 'max' => 5],
        'hours' => ['min' => 9, 'max' => 17]
    ];

    public function validateAttributes($attributes) {
        foreach($attributes as $attributeKey => $attributeValue) {
	    //validate start_time field
	    if($attributeKey == 'start_time') {
	        if(!(\DateTime::createFromFormat('Y-m-d H:i:s', $attributeValue))) { throw new InvalidDateFormatReservationException; }
            }
	    //validate duration_in_minutes attribute
	    if($attributeKey == 'duration_in_minutes') {
		$minutes = intval($attributeValue);
		if($minutes < 30) { throw new DurationTooShortReservationException; }
		if($minutes > 120) { throw new DurationTooLongReservationException; }
		if($minutes % 15 != 0) { throw new InvalidDurationReservationException; }
            }
        }

	return true;
    }

    public function getStartTime($attributes){
        //validate attributes first
        $this->validateAttributes($attributes);

        return $attributes['start_time'];
    }

    public function getEndTime($attributes) {
	//validate attributes first
	$this->validateAttributes($attributes);
        //calculate end time
        $endTime = \DateTime::createFromFormat('Y-m-d H:i:s', $attributes['start_time']);
	$minutes = $attributes['duration_in_minutes'];
	$endTime->modify('+'.$minutes.' minutes');

        return $endTime->format('Y-m-d H:i:s');
    }

    public function getAvailableMasseur($attributes) {
	$startTime = $this->getStartTime($attributes);
	$endTime = $this->getEndTime($attributes);
	$masseurs = Masseur::all();
	$available = false;
	//loop every maaseur
	foreach($masseurs as $masseur) {
            $data = Reservation::where('masseur_id', $masseur->id)->
	                where(function($query) use ($startTime, $endTime){
		            $query->where(function($query) use ($startTime, $endTime){
                                $query->where('session_start_time', '>', $startTime);
                                $query->where('session_start_time', '<', $endTime);
                            });
                            $query->orWhere(function($query) use ($startTime, $endTime){
                                $query->where('session_start_time', '<', $endTime);
                                $query->where('session_end_time', '>', $startTime);
                            });
                            $query->orWhere(function($query) use ($startTime, $endTime){
                                $query->where('session_start_time', '<', $startTime);
                                $query->where('session_end_time', '>', $endTime);
                            });
	                });
	    if($data->count() == 0) {
                $available = $masseur;
                break;
            }
        }

        if(!$available) { throw new SessionNotAvailableReservationException; }

        return $available;
    }

    public function checkIfWorkingHours($attributes) {
        //get values
        $startTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getStartTime($attributes));
	$endTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getEndTime($attributes));
	//assign
	$startTimeDay = $startTime->format('N');
	$startTimeHours = $startTime->format('H');
	$endTimeDay = $endTime->format('N');
	$endTimeHours = $endTime->format('H');
	$endTimeMinutes = $endTime->format('i');

	//check day of the week(N) and hours(H)
        if(($startTimeDay > $this->workingHours['day']['max'] || $startTimeDay < $this->workingHours['day']['min']) ||
           ($startTimeHours > $this->workingHours['hours']['max'] || $startTimeHours < $this->workingHours['hours']['min']) ||
	   ($endTimeDay > $this->workingHours['day']['max'] || $endTimeDay < $this->workingHours['day']['min']) ||
           ($endTimeHours > $this->workingHours['hours']['max'] || ($endTimeHours == $this->workingHours['hours']['max'] && $endTimeMinutes > 0) || $endTimeHours < $this->workingHours['hours']['min'])){ 
	    throw new SessionNotAvailableReservationException;
	}

        return true;
    }
}
