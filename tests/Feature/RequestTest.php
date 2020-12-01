<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Reservation;
use App\Models\Masseur;

class RequestTest extends TestCase
{
    /** @test */
    public function check_if_reservations_are_created_correctly()
    {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
            'start_time' => '2020-11-02 10:00:00',
            'duration_in_minutes' => 45,
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(201)
            ->assertJson(['session_start_time' => '2020-11-02 10:00:00', 'session_end_time' => '2020-11-02 10:45:00', 'masseur_id' => 1]);
    }

    /** @test */
    public function check_if_reservations_are_listed_correctly(){
	Reservation::factory()->create([
            'session_start_time' => '2020-11-02 14:00:00',
            'session_end_time' => '2020-11-02 14:45:00',
	    'masseur_id' => 1
        ]);

	Reservation::factory()->create([
            'session_start_time' => '2020-11-02 14:00:00',
            'session_end_time' => '2020-11-02 14:45:00',
	    'masseur_id' => 2
        ]);

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];

        $response = $this->json('GET', '/api/reservations', [], $headers)
            ->assertStatus(200)
            ->assertJson([
                [ 'session_start_time' => '2020-11-02 14:00:00', 'session_end_time' => '2020-11-02 14:45:00' , 'masseur_id' => 1],
                [ 'session_start_time' => '2020-11-02 14:00:00', 'session_end_time' => '2020-11-02 14:45:00' , 'masseur_id' => 2],
            ])
            ->assertJsonStructure([
                '*' => ['id', 'session_start_time', 'session_end_time', 'masseur_id', 'created_at', 'updated_at'],
            ]);
    }

    /** @test */
    public function check_if_duration_maximum_length_error_is_returned() {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
            'start_time' => '2020-11-02 10:00:00',
            'duration_in_minutes' => 134,
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(404)
            ->assertJson(['error' => 'Duration is over 120 minutes (2 hours).']);
    }

    /** @test */
    public function check_if_duration_minimum_length_error_is_returned() {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
            'start_time' => '2020-11-02 10:00:00',
            'duration_in_minutes' => 3,
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(404)
            ->assertJson(['error' => 'Duration is not over 30 minutes.']);
    }

    /** @test */
    public function check_if_invalid_date_error_is_returned() {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
            'start_time' => 'xxx',
            'duration_in_minutes' => 30,
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(404)
            ->assertJson(['error' => 'Invalid date format.']);
    }

    /** @test */
    public function check_if_missing_start_time_error_is_returned() {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
            'duration_in_minutes' => 30,
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(404)
            ->assertJson(['error' => 'Start time not found.']);
    }

    /** @test */
    public function check_if_missing_duration_error_is_returned() {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
	    'start_time' => '2020-11-02 10:00:00'
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(404)
            ->assertJson(['error' => 'Session duration not found.']);
    }

    /** @test */
    public function check_if_session_not_available_error_is_returned() {
	//all masseurs book same time
	$masseurs = Masseur::all();
	foreach($masseurs as $masseur) {
	    Reservation::factory()->create([
                'session_start_time' => '2020-11-02 10:00:00',
                'session_end_time' => '2020-11-02 10:45:00',
                'masseur_id' => $masseur->id
            ]);
        }

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
            'start_time' => '2020-11-02 10:00:00',
            'duration_in_minutes' => 30,
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(404)
            ->assertJson(['error' => 'Session not available.']);
    }

    /** @test */
    public function check_if_weekend_session_not_available_error_is_returned() {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = [
            'start_time' => '2020-11-01 10:00:00',
            'duration_in_minutes' => 30,
        ];

        $this->json('POST', '/api/reservations', $payload, $headers)
            ->assertStatus(404)
            ->assertJson(['error' => 'Session not available.']);
    }
}
