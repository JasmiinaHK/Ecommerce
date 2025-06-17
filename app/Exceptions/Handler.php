<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
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
            // Log all exceptions
            \Log::error('Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });

        // Handle specific error: Attempt to read property 'id' on null
        $this->renderable(function (\ErrorException $e, $request) {
            if (str_contains($e->getMessage(), 'Attempt to read property "id" on null')) {
                \Log::error('Null ID access detected', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'user' => auth()->check() ? auth()->id() : 'guest',
                    'input' => $request->all()
                ]);

                // Return a more user-friendly error page
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'An error occurred while processing your request.',
                        'error' => 'Invalid data reference'
                    ], 500);
                }

                return redirect()->back()->withErrors([
                    'error' => 'An error occurred. Please try again.'
                ]);
            }
            
            return null; // Let Laravel handle other errors
        });
    }
}
