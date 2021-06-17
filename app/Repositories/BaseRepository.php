<?php
namespace App\Repositories;

use App\Core\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaseRepository
{
	// model and request property on class instances
	protected $request;

	protected $model;

	protected $userRole = ['admin', 'user'];

	protected $helper;

	// Constructor to bind model to repo

	public function __construct($model = null)
    {
		$this->request = app('request');

		$this->model = $model;

		$this->helper = app(Helper::class);
	}

	// Get all instances of model
	public function all()
	{
		return $this->model->all();
	}
	// create a new record in the database
	public function create(array $data)
	{
		return $this->model->create($data);
	}
	// update record in the database
	public function update(array $data, $id)
	{
		$record = $this->model->find($id);

		return $record->update($data);
	}
	// remove record from the database
	public function delete($id)
	{
		return $this->model->destroy($id);
	}
	// show the record with the given id
	public function getById($id)
	{
		return $this->model->find($id);
	}
	// show the record with the given id
	public function getByColumn($column, $value)
	{
		return $this->model->where($column,$value)->get();
	}
	// show the record with the given id
	public function updateByColumn($where, $values)
	{
		 $record= $this->model->where($where)->first();

		foreach ($values as $key =>$value){
			$record->$key = $value;
		}

		$record->save();

		return $record;
	}
	public function upSert($where, $values)
	{
		$this->model->updateOrCreate($where,
			$values);
	}

	// show the record with the given id
	public function deleteByColumn($column, $value)
	{
		return $this->model->where($column, $value)->delete();
	}
	// Get the associated model
	public function getModel()
	{
		return $this->model;
	}
	// Set the associated model
	public function setModel($model)
	{
		$this->model = $model;
		return $this;
	}
	// Set the customer profile id
	public function setCustProfileId($custprofileid)
	{
		$this->custprofileid = $custprofileid;
		return $this;
	}
	// Eager load database relationships
	public function withRelation($relations)
	{
		//with('team1', 'team2')
		return $this->model->with($relations);
	}

	/**
	 * @param array $filters
	 * @param array $sortby
	 * @param array $limit
	 * @return mixed multidimentional
	 */
	public function getFilterData($filters=[], $sortby=[], $limit=[])
    {
		$query = $this->model->query();

		/*
		each subarray of filter can contain the following indexes

		col = column
		val = value to match, in case of op => IN/NOTIN val will contain an array of elements [$n1, $n2 ... , $nx]
		op = operator, default '='
		log = AND/OR , default 'AND'

		e.g.

		[
			['col'=>'user_id','val'=>$user_id]
			['col'=>'id','op'=>'>','val'=>$user_id]
			['col'=>'lastname','op'=>'<','val'=>$user_id , 'log'=>'OR']
			['col'=>'gender','op'=>'IN','val'=>[$user_id,$user_id1] ]
		]
		*/
        if(!empty($filters))
        {
        	$totfilters = count($filters);
        	for ($f=0; $f < $totfilters ; $f++) {
                $oper = ($filters[$f]['op'] ?? '=');
                $logic = ($filters[$f]['log'] ?? 'AND');

                if($oper == 'NOTIN') {
                    $query->whereNotIn($filters[$f]['col'],$filters[$f]['val']);
                } else if($oper == 'IN') {
                    $query->whereIn($filters[$f]['col'],$filters[$f]['val']);
                } else if($logic == 'OR') {
        		    $query->orWhere($filters[$f]['col'],$oper,$filters[$f]['val']);
                } else {
        		    $query->where($filters[$f]['col'],$oper,$filters[$f]['val']);
                }
        	}
        }

		/*
		e.g.

		[
			['user_id'=>'ASC','createdtime'=>'DESC']
			['ORDERBY_FIELD'=>['col'=>'firstname','vals'=>['a','b','c'] ]]
		]
		*/
        if(!empty($sortby)) {
        	foreach ($sortby as $key => $value) {
                if($key == 'ORDERBY_FIELD') {
                  $query->orderByRaw( "FIELD(". $value['col'] .", '". implode("', '", $value['vals']) ."') DESC" );
                } else {
        		  $query->orderBy($key, $value);
                }
        	}
        }

        if(!empty($limit)) {

        	$limit 	= ( $limit['limit'] ?? 10);
        	$page 	= ( $limit['page'] ?? 1);

			$query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $query->get();
    }
	/**
	 * @param $column
	 * @param $values
	 * @return mixed
	 */
	public function getWhereInData($column, $values)
	{
		return $this->model->whereIn($column,$values)->get();
	}

	/**
	 * @param $column
	 * @param $values
	 * @return mixed
	 */
	public function getWhereNotInData($column, $values)
	{
		return $this->model->whereNotIn($column,$values)->get();
	}

	/**
	 * @param array $where
	 * @return mixed
	 */
	public function getCount($where=[])
	{
		$query = $this->model->query();

		if(count($where)){
			$query->where($where);
		}

		return $query->count();
	}

	/**
	 * @param $page
	 * @param $limit
	 * @param array $where
	 * @return mixed
	 */
	public function getDataByPagination($page, $limit, $where=[])
	{
		$query = $this->model->query();

		if(count($where)){
			$query->where($where);
		}

		$query->limit($limit);

		return $query->offset(($page - 1) * $limit)->get();

	}

	/**
	 * success response method.
	 *
	 * @return \Illuminate\Http\Response
	 */
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


	/**
	 * return error response.
	 *
	 * @return \Illuminate\Http\Response
	 */
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

	public function validated($rules, $request)
	{
		$customMessages = [
			'required' => 'The :attribute field is required.'
		];

		return Validator::make($request, $rules, $customMessages);

	}

	public function ApplyHTMLEntities($params)
	{
		if( is_array($params))
		{
			return array_map([$this, __METHOD__], $params);
		}
		else if(is_string($params))
		{
			return htmlspecialchars($params);
		}
		else if(is_a($params, 'Illuminate\Database\Eloquent\Collection'))
		{
			return array_map([$this, __METHOD__], $params->toArray());
		}
		/*else if(is_a($params, 'stdClass'))
		{

			return array_map([$this, __METHOD__], json_decode(json_encode($params), true));
		}*/
		else
		{
			return $params;
		}
	}
}
