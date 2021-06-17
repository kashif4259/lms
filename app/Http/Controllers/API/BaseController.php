<?php

namespace App\Http\Controllers\API;

use App\Core\Helper;
use App\Core\YelpApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    protected $request;

    protected $yelpApi;

    protected $helper;
    
    public function __construct()
    {
        $this->request = app('request');

        $this->yelpApi = app(YelpApi::class);

        $this->helper = app(Helper::class);
    }

    public function sendResponse($data, $code = 200, $message = 'success', $httpCode = 200, $applyencoding=true)
	{
		if($applyencoding)
		{
			$data = $this->ApplyHTMLEntities($data);
		}

		$response = [
			'message' => $message,
			'code' => $code,
			'data'    => $data
		];

		return response()->json($response, $httpCode);
	}

    public function sendError($errorMsg,  $errorcode = 404, $httpCode =200, $errorData = [])
	{
		$response = [
			'errors' => $errorMsg,
			'code' => $errorcode
		];


		if(!empty($errorData)){
			$errorData = $this->ApplyHTMLEntities($errorData);
			$response['data'] = $errorData;
		}


		return response()->json($response, $httpCode);
	}

    public function applyHtmlEntities($params)
    {
        $response = $params;

        if(is_array($params))
        {
            $response = array_map([$this, __METHOD__], $params);
        }
        
        else if(is_string($params))
        {
            $response = htmlspecialchars($params);
        }

        else if(is_a($params, 'Illuminate\Database\Eloquent\Collection'))
        {
            $response = array_map([$this, __METHOD__], $params->toArray());
        }

        return $response;
    }

    public function validated($rules, $request)
	{
		$customMessages = [
			'required' => 'The :attribute field is required.'
		];

		return Validator::make($request, $rules, $customMessages);

	}
}
