<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

use Illuminate\Http\Exception\HttpResponseException;
use \Illuminate\Database\QueryException;
use Illuminate\Http\Response;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ValidationException::class

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->wantsJson())
        {   
            return $this->handleApiException($request, $exception);
        }
        
        return parent::render($request, $exception);

        // var_dump($request->wantsJson());
        // // Give detailed stacktrace error info if APP_DEBUG is true in the .env
        // if ($request->wantsJson()) {

        //     $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        //     if ($e instanceof HttpResponseException)
        //     {
        //         $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        //         var_dump($e->getMessage());
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        //     elseif ($e instanceof MethodNotAllowedHttpException)
        //     {
        //         $status = Response::HTTP_METHOD_NOT_ALLOWED;
        //             //$e = new MethodNotAllowedHttpException([], 'HTTP_METHOD_NOT_ALLOWED', $e);
        //             return response()->json([
        //                 'message' => 'HTTP_METHOD_NOT_ALLOWED',
        //                 'code' => $status,
        //                 'data' => []
        //             ], $status);
        //         }
        //     elseif ($e instanceof NotFoundHttpException)
        //     {

        //         $status = Response::HTTP_NOT_FOUND;
        //         //$e = new NotFoundHttpException('HTTP_NOT_FOUND', $e);
        //         return response()->json([
        //             'message' => 'HTTP_NOT_FOUND',
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        //     elseif ($e instanceof AuthorizationException)
        //     {

        //         $status = Response::HTTP_FORBIDDEN;
        //         //$e = new AuthorizationException('HTTP_FORBIDDEN', $status);
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        //     elseif ($e instanceof \Dotenv\Exception\ValidationException && $e->getResponse())
        //     {

        //         $status = Response::HTTP_BAD_REQUEST;
        //         //$e = new \Dotenv\Exception\ValidationException('HTTP_BAD_REQUEST', $status, $e);
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        //     elseif ($e instanceof QueryException)
        //     {
        //         //dont do anything here
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        //     elseif ($e instanceof \PDOException)
        //     {
        //         //dont do anything here
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        //     elseif ($e instanceof HttpException)
        //     {
            
        //         //$e = new HttpException($status, $e->getMessage());
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        //     elseif ($e instanceof Throwable)
        //     {
        //         $errormsg = '';

        //         if($e->getPrevious())
        //         {
        //             $errormsg = $e->getPrevious()->getMessage(). " at line ". $e->getPrevious()->getLine(). " on file ". $e->getPrevious()->getFile();
        //         }
        //         else
        //         {
        //             $errormsg = $e->getMessage(). " at line ". $e->getLine(). " on file ". $e->getFile();
        //         }

        //         return response()->json([
        //             'message' => $errormsg,
        //             'code' => $status,
        //             'data' => []
        //         ], $status);
        //     }
        // }
        // return parent::render($request, $exception);
    }

    private function handleApiException($request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof \Illuminate\Http\Exception\HttpResponseException) {
            $exception = $exception->getResponse();
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }

        return $this->customApiResponse($exception);
    }

    private function customApiResponse($exception)
    {
        if (method_exists($exception, 'getStatusCode')) 
        {
            $statusCode = $exception->getStatusCode();
        }
        else 
        {
            $statusCode = 500;
        }

        $response = [];

        switch ($statusCode) {
            case 401:
                $response['message'] = 'Unauthorized';
                break;
            case 403:
                $response['message'] = 'Forbidden';
                break;
            case 404:
                $response['message'] = 'Not Found';
                break;
            case 405:
                $response['message'] = 'Method Not Allowed';
                break;
            case 422:
                $response['message'] = $exception->original['message'];
                $response['errors'] = $exception->original['errors'];
                break;
            default:

                // $errormsg = '';
                // dd($exception->getMessage());
                // if($exception->getPrevious())
                // {
                //     $errormsg = $e->getPrevious()->getMessage(). " at line ". $exception->getPrevious()->getLine(). " on file ". $exception->getPrevious()->getFile();
                // }
                // else
                // {
                //     $errormsg = $e->getMessage(). " at line ". $exception->getLine(). " on file ". $exception->getFile();
                // }
                $response['message'] = ($statusCode == 500) ? $exception->getMessage()  : 'Whoops, looks like something went wrong';
                break;
        }

        if (config('app.debug')) 
        {
            $response['trace'] = $exception->getTrace();
            $response['code'] = $exception->getCode() === 0 ? $statusCode : $exception->getCode();
        }

        $response['status'] = $statusCode;

        return response()->json($response, $statusCode);
    }

}
