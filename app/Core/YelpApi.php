<?php

namespace App\Core;

use Log;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class YelpApi
{
	protected $base_url;
	protected $request;
	protected $headers;
	protected $apikey;
    protected $api_version = 'v3';
    protected $handler;

	public function __construct()
	{
		$this->base_url = env('YELP_API_URL');

		$this->apikey = env('YELP_API_KEY');

		// if(env('APP_ENV') == 'local')
		// {
		// 	$this->base_url_start .= 'dev';
		// 	$this->base_url_stop .= 'dev';
		// }

		// $this->headers = [
		// 	'Authorization' => 'Bearer '.$this->apikey
		// ];

        $this->headers = [
			// 'Content-Type' => 'application/json',
			// 'Accept'        => 'application/json',
            'Authorization' => 'Bearer '.$this->apikey
		];

		$this->request = app('request');

        $this->handler = app(Handler::class);

        $this->base_url .= $this->api_version.'/';

	}

	public function searchBusinesses($offset)
	{
		$params = [
		    "term" => $this->request->term,
		    "location" => $this->request->location,
            'offset' => $offset,
            'limit' => 50
		];

        if($response = $this->endpointRequest('GET', $this->base_url.'businesses/search', $params))
		{
			Log::Info('apigatway wrapper');
			Log::Info($response);
			if($response['code'] == 200)
			{
				return $this->sendResponse($response['data'], 200);
			}

			return self::sendError($response['msg'], $response['code'], 401);
		}

		return $this->sendError('Service Unavailable', 8001, 503);
	}

    public function endpointRequest($request_type, $url, $params = [])
	{
		try {
            $options = [
                'query' => $params
            ];
			//  $params = json_encode($params);

			$this->client = new Client(['headers' => $this->headers, 'verify' => false]);

			//Log::Info($url);
			$response = $this->client->request($request_type, $url, $options);
			$statusCode = $response->getStatusCode();

			$body = $response->getBody();

		} catch (ClientException $e) {
            $exception = json_decode($e->getResponse()->getBody());
            $msg = $exception->error->description;
            return ['code' => $e->getCode(), 'msg' => $msg];
			// Log::critical('ClientException');
			// Log::critical($e->getResponse()->getBody());
			// $exception = json_decode($e->getResponse()->getBody());
            // return [ 'msg' => $this->responseHandler($body),'code'=>$status];
            // return $exception;
			// $exception->addition_info= ['line'=>__LINE__,'file'=>__FILE__,'exptype'=>'RiskAssessment/ClientException','parmas'=>$params];
			//return $exception;
			// return $this->handler->returnException($exception , $e->getResponse()->getStatusCode(), true, true);

		} catch (RequestException $e) {
            $exception = json_decode($e->getResponse()->getBody());
            $msg = $exception->error->description;
            return ['code' => $e->getCode(), 'msg' => $msg];
			// Log::critical('RequestException');
			// Log::critical($e->getResponse()->getBody());
			// $exception = json_decode($e->getResponse()->getBody());
            // return $exception;
			//return $exception;
			// $exception->addition_info= ['line'=>__LINE__,'file'=>__FILE__,'exptype'=>'RiskAssessment/RequestException','parmas'=>$params];
			// return $this->handler->returnException($exception , $e->getResponse()->getStatusCode(), true, true);

		} catch (Exception $e) {
            $exception = json_decode($e->getResponse()->getBody());
            $msg = $exception->error->description;
            return ['code' => $e->getCode(), 'msg' => $msg];
			// Log::critical('Exception');
			// Log::critical($e->getResponse()->getBody());
			// $exception = json_decode($e->getResponse()->getBody());
            // return $exception;
			//return $exception;
			// $exception->addition_info= ['line'=>__LINE__,'file'=>__FILE__,'exptype'=>'RiskAssessment/Exception','parmas'=>$params];
			// return $this->handler->returnException($exception , $e->getResponse()->getStatusCode(), true, true);

		}
        return [ 'data' => $this->responseHandler($body),'code'=>$statusCode];
	}

    public function responseHandler($response)
	{
		if($response)
		{
			return json_decode($response);
		}

		return [];
	}

    public function sendError($error, $errorcode = 404, $httpcode = 404, $errorData = [])
	{
		$response = [
			'message' => $error,
			'code' => $errorcode
		];


		if (!empty($errorData)) {
			$response['data'] = $errorData;
		}


		return response()->json($response, $httpcode);
	}

    public function sendResponse($data, $code = 200, $message = 'success')
	{
		$response = [
			'message' => $message,
			'code' => $code,
			'data' => $data
		];


		return response()->json($response, 200);
	}
}