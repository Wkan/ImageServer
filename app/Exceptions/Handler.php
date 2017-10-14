<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Intervention\Image\Exception\NotReadableException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     *
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $e)
    {
        $response = [
            'message' => 'Whoops, looks like something went wrong.',
            'error'  => 500
        ];

        if ($e instanceof HttpException) {
            $response['message'] = Response::$statusTexts[$e->getStatusCode()];
            $response['error'] = $e->getStatusCode();
        } elseif ($e instanceof ModelNotFoundException) {
            $response['message'] = Response::$statusTexts[Response::HTTP_NOT_FOUND];
            $response['error'] = Response::HTTP_NOT_FOUND;
        } elseif ($e instanceof NotReadableException) {
            $response['message'] = 'Not Found Image.';
            $response['error'] = Response::HTTP_NOT_FOUND;
        }

        if (env('APP_DEBUG', config('app.debug', false))) {
            $fe = FlattenException::create($e);
            $response['debug'] = $fe->toArray();
        }


        return response()->json($response, $response['error'], [], JSON_UNESCAPED_UNICODE);
    }
}
