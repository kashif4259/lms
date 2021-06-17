<?php

namespace App\Repositories;

use App\Models\Department;
use App\Repositories\BaseRepository as BaseRepository;

class DepartmentRepository extends BaseRepository
{

	public function __construct()
	{
        parent::__construct(app(Department::class));
	}

    public function getDepartments()
    {
        $query = $this->model->query();
        
        $term = $this->request->get('q');

        if($term)
        {
            $departments = $query->where(function($q) use ($term){
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('dean', 'LIKE', "%{$term}%");
            })->paginate(10);
        }
        else
        {
            $departments = $this->model->latest()->paginate(10);
        }

        $data = json_decode(json_encode($departments),true);

        $sr_no = $this->helper::GenerateSrNo($data['current_page'], 10);

        foreach($departments as &$department)
        {
            $department->sr_no = $sr_no;
            
            $sr_no++;
        }

        return $this->sendResponse($departments);
    }

    public function createDepartment()
    {
        $rules = [
			'name' => 'required|unique:departments|min:5|max:150',
            'dean' => 'required|string|min:5|max:150'
		];

		$validation = $this->validated($rules, $this->request->all());

		if ($validation->fails()) 
        {
			return $this->sendError($validation->errors(), 9001, 422);
		}
        
        if($department = $this->model->create([
            'name' => $this->request->name,
            'dean' => $this->request->dean
        ]))
        {
            return $this->sendResponse($department, 200, 'Department created successfully!', 201);
        }

        return $this->sendError("Something went wrong", 9000, 404);
    }

    public function modify($id)
    {
        $department = $this->model->findOrFail($id);

        if(!$department)
        {
            return $this->sendError("Department not found", 9600, 404);
        }

        $rules = [
			'name' => 'required|min:5|max:150|unique:departments,name,'.$department->id,
            'dean' => 'required|string|min:5|max:150'
		];

        $validation = $this->validated($rules, $this->request->all());

		if ($validation->fails()) 
        {
			return $this->sendError($validation->errors(), 9001, 422);
		}

        $department->update($this->request->all());

        return $this->sendResponse($department, 200, 'Department updated successfully!');
    }

    public function destroy($id)
    {
        $department = $this->model->findOrFail($id);

        if($department && $department->delete())
        {
            return $this->sendResponse([], 200, 'Department deleted successfully');
        }

        return $this->sendError("Department not found", 9000, 401);
    }
}
