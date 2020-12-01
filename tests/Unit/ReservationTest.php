<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Reservation;
use App\Models\Masseur;
//Reservation Exceptions
use App\Exceptions\Reservation\StartTimeNotFoundReservationException;
use App\Exceptions\Reservation\DurationNotFoundReservationException;
use App\Exceptions\Reservation\InvalidDateFormatReservationException;
use App\Exceptions\Reservation\DurationTooShortReservationException;
use App\Exceptions\Reservation\DurationTooLongReservationException;
use App\Exceptions\Reservation\InvalidDurationReservationException;
use App\Exceptions\Reservation\SessionNotAvailableReservationException;

class ReservationTest extends TestCase
{
    /** @test */
    public function check_if_validating_attributes_correctly(){
	 $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 30];
	 $reservation = new Reservation;
	 $this->assertTrue($reservation->validateAttributes($attributes));

	 $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 60];
         $reservation = new Reservation;
         $this->assertTrue($reservation->validateAttributes($attributes));

	 $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 75];
         $reservation = new Reservation;
         $this->assertTrue($reservation->validateAttributes($attributes));
    }

    /** @test */
    public function check_if_invalid_date_format_exception_is_thrown(){
       	 $attributes = ['start_time' => 'xxxx', 'duration_in_minutes' => 30];
         $reservation = new Reservation;
         $this->expectException(InvalidDateFormatReservationException::class);

	 $reservation->validateAttributes($attributes);
    }

    /** @test */
    public function check_if_duration_too_short_exception_is_thrown(){
         $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 2];
         $reservation = new Reservation;
         $this->expectException(DurationTooShortReservationException::class);

         $reservation->validateAttributes($attributes);
    }

    /** @test */
    public function check_if_duration_too_long_exception_is_thrown(){
         $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 200];
         $reservation = new Reservation;
         $this->expectException(DurationTooLongReservationException::class);

         $reservation->validateAttributes($attributes);
    }

    /** @test */
    public function check_if_invalid_duration_exception_is_thrown(){
         $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 37];
         $reservation = new Reservation;
         $this->expectException(InvalidDurationReservationException::class);

         $reservation->validateAttributes($attributes);
    }

    /** @test */
    public function check_if_returns_correct_start_time(){
	 $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 30];
         $reservation = new Reservation;

	 $this->assertEquals('2020-11-02 14:00:00', $reservation->getStartTime($attributes));
    }

    /** @test */
    public function check_if_returns_correct_end_time(){
         $attributes = ['start_time' => '2020-11-02 14:00:00', 'duration_in_minutes' => 30];
         $reservation = new Reservation;

         $this->assertEquals('2020-11-02 14:30:00', $reservation->getEndTime($attributes));
    }

    /** @test */
    public function check_if_out_of_working_hours_session_not_available_exception_is_thrown(){
	 $attributes = ['start_time' => '2020-11-02 17:00:00', 'duration_in_minutes' => 30];
         $reservation = new Reservation;
         $this->expectException(SessionNotAvailableReservationException::class);

         $reservation->checkIfWorkingHours($attributes);

	 $attributes = ['start_time' => '2020-11-02 01:00:00', 'duration_in_minutes' => 30];
         $reservation = new Reservation;
         $this->expectException(SessionNotAvailableReservationException::class);

         $reservation->checkIfWorkingHours($attributes);
    }

    /** @test */
    public function check_if_in_working_hours_returns_true(){
         $attributes = ['start_time' => '2020-11-02 10:00:00', 'duration_in_minutes' => 30];
         $reservation = new Reservation;

	 $this->assertTrue($reservation->checkIfWorkingHours($attributes));
    }

    /** @test */
    public function check_if_masseurs_are_fully_booked(){
         //all masseurs book same time
         $masseurs = Masseur::all();
         foreach($masseurs as $masseur) {
             Reservation::factory()->create([
                 'session_start_time' => '2020-11-02 10:00:00',
                 'session_end_time' => '2020-11-02 10:45:00',
                 'masseur_id' => $masseur->id
             ]);
         }

	 $attributes = ['start_time' => '2020-11-02 10:00:00', 'duration_in_minutes' => 30];
         $reservation = new Reservation;
         $this->expectException(SessionNotAvailableReservationException::class);

	 $reservation->getAvailableMasseur($attributes);

	 $attributes = ['start_time' => '2020-11-02 10:44:00', 'duration_in_minutes' => 30];
         $reservation = new Reservation;
         $this->expectException(SessionNotAvailableReservationException::class);

	 $reservation->getAvailableMasseur($attributes);
    }

    /** @test */
    public function check_if_finds_masseur(){
         Reservation::factory()->create([
                 'session_start_time' => '2020-11-02 10:00:00',
                 'session_end_time' => '2020-11-02 10:45:00',
                 'masseur_id' => 1
             ]);

	  Reservation::factory()->create([
                 'session_start_time' => '2020-11-02 10:00:00',
                 'session_end_time' => '2020-11-02 10:45:00',
                 'masseur_id' => 2
             ]);

	  $attributes = ['start_time' => '2020-11-02 10:00:00', 'duration_in_minutes' => 45];
	  $reservation = new Reservation;

	  $masseur = $reservation->getAvailableMasseur($attributes);
	  var_dump($masseur);
	  $this->assertInstanceOf('App\Models\Masseur', $masseur);
	  $this->assertEquals(3,$masseur->id);
    }
}
