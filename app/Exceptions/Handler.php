<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//Reservation Exceptions
use App\Exceptions\Reservation\StartTimeNotFoundReservationException;
use App\Exceptions\Reservation\DurationNotFoundReservationException;
use App\Exceptions\Reservation\InvalidDateFormatReservationException;
use App\Exceptions\Reservation\DurationTooShortReservationException;
use App\Exceptions\Reservation\DurationTooLongReservationException;
use App\Exceptions\Reservation\InvalidDurationReservationException;
use App\Exceptions\Reservation\SessionNotAvailableReservationException;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {    
	if ($exception instanceof StartTimeNotFoundReservationException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Start time not found.'
            ], 404);
        }

	if ($exception instanceof DurationNotFoundReservationException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Session duration not found.'
            ], 404);
        }

	if ($exception instanceof InvalidDateFormatReservationException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Invalid date format.'
            ], 404);
        }

	if ($exception instanceof InvalidDurationReservationException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Invalid duration.'
            ], 404);
        }

	if ($exception instanceof DurationTooShortReservationException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Duration is not over 30 minutes.'
            ], 404);
        }

	if ($exception instanceof DurationTooLongReservationException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Duration is over 120 minutes (2 hours).'
            ], 404);
        }

	if ($exception instanceof SessionNotAvailableReservationException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Session not available.'
            ], 404);
        }


        // This will replace our 404 response with a JSON response.
        if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Resource item not found.'
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Resource not found.'
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException && $request->wantsJson()) {
            return response()->json([
                'error' => 'Method not allowed.'
            ], 405);
        }


        return parent::render($request, $exception);
    }

}
