<?php

namespace App\Repositories;


use App\Models\User;
use App\Repositories\BaseRepository as BaseRepository;

class UserRepository extends BaseRepository
{

	public function __construct()
	{
        parent::__construct(app(User::class));
	}

    public function getMembers()
    {
        $query = $this->model->query();
        
        $term = $this->request->get('q');

        if($term)
        {
            $users = $query->where('role', '<>', 'admin')->where(function($q) use ($term){
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('role', 'LIKE', "%{$term}%");
            })->latest()->paginate(10);

            return $this->sendResponse($users);
        }

        $users = $this->model->where('role', '<>', 'admin')->latest()->paginate(10);

        $data = json_decode(json_encode($users),true);

        $sr_no = $this->helper::GenerateSrNo($data['current_page'], 10);
        
        foreach($users as &$user)
        {
            $user->sr_no = $sr_no;

            $sr_no++;
        }

        return $this->sendResponse($users);
    }

    public function createMember()
    {
        $rules = [
			'name' => 'required|min:5|max:150',
			'email' => 'required|email|unique:users|max:150',
			'role' => 'required|in:' . implode(',', $this->userRole),
            'password' => 'required|string|max:150',
            'bio' => 'nullable|string',
            'photo' => 'nullable|max:150'
		];

		$validation = $this->validated($rules, $this->request->all());

		if ($validation->fails()) 
        {
			return $this->sendError($validation->errors(), 9001, 422);
		}

        if($user = $this->model->create([
            'name' => $this->request->name,
            'email' => $this->request->email,
            'role' => $this->request->role,
            'password' => $this->request->password,
            'bio' => $this->request->bio,
            'photo' => 'default.png'
        ]))
        {
            return $this->sendResponse($user, 200, 'User created successfully!', 201);
        }

        return $this->sendError("Something went wrong", 9000, 404);
    }

    public function modify($id)
    {
        $user = $this->model->findOrFail($id);

        if(!$user)
        {
            return $this->sendError("User not found", 9600, 404);
        }

        $rules = [
			'name' => 'required|min:5|max:150',
			'email' => 'required|email|max:150|unique:users,email,'.$user->id,
			'role' => 'required|in:' . implode(',', $this->userRole),
            'bio' => 'nullable|string',
            'photo' => 'nullable|max:150'
		];

        $validation = $this->validated($rules, $this->request->all());

		if ($validation->fails()) 
        {
			return $this->sendError($validation->errors(), 9001, 422);
		}

        $user->update($this->request->all());

        return $this->sendResponse($user, 200, 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = $this->model->findOrFail($id);

        $userPhoto = public_path('img/profile/').$user->photo;

        if($user && $user->delete())
        {
            if(file_exists($userPhoto))
            {
                @unlink($userPhoto);
            }

            return $this->sendResponse([], 200, 'User deleted successfully');
        }

        return $this->sendError("User not found", 9000, 401);
    }

    public function profile()
    {
        $profile = auth('api')->user();
        return $this->sendResponse($profile);
    }

    public function updateProfile()
    {
        $user = auth('api')->user();

        $rules = [
			'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|min:6'
		];

        $validation = $this->validated($rules, $this->request->all());

		if ($validation->fails()) 
        {
			return $this->sendError($validation->errors(), 9001, 422);
		}

        $currentPhoto = $user->photo;

        if($this->request->photo != $currentPhoto)
        {
            $name = time().'.' . explode('/', explode(':', substr($this->request->photo, 0, strpos($this->request->photo, ';')))[1])[1];
            
            \Image::make($this->request->photo)->save(public_path('img/profile/').$name);

            $this->request->merge([
                'photo' => $name
            ]);

            $userPhoto = public_path('img/profile/').$currentPhoto;
            
            if(file_exists($userPhoto))
            {
                @unlink($userPhoto);
            }
        }

        if(!empty($this->request->password))
        {
            $this->request->merge(['password' => Hash::make($this->request['password'])]);
        }

        $user->update($this->request->all());

        return $this->sendResponse($user, 200, 'User profile update successfully');
    }
}
