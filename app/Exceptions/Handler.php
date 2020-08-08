<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $rendered = parent::render($request, $exception);

        if ($request->expectsJson()) {
           if ($exception instanceof NotFoundHttpException) {
               return response()->json([
                   'meta' => ['status' => 0, 'code' => $rendered->getStatusCode(), 'message' => 'Not Found']
               ], $rendered->getStatusCode());
           } elseif ($exception instanceof ValidationException) {
               return response()->json([
                   'meta' => ['status' => 0, 'code' => $rendered->getStatusCode(), 'message' => array_values($exception->errors())[0][0]]
               ], $rendered->getStatusCode());
           } else {
               return response()->json([
                   'meta' => ['status' => 0, 'code' => $rendered->getStatusCode(), 'message' => $exception->getMessage()]
               ], $rendered->getStatusCode());
           }
        }
        return $rendered;
    }
}
