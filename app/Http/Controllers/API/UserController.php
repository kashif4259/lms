<?php

namespace App\Http\Controllers\API;

use App\Repositories\UserRepository;
use App\Http\Controllers\API\BaseController as BaseController;

class UserController extends BaseController
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return $this->userRepository->getMembers();
    }

    public function create()
    {
        return $this->userRepository->createMember();
    }

    public function update($id)
    {
        return $this->userRepository->modify($id);
    }
   
    public function destroy($id)
    {
        return $this->userRepository->destroy($id);
    }

    public function profile()
    {
        return $this->userRepository->profile();
    }

    public function search()
    {
        return $this->userRepository->search();
    }

    public function updateProfile($id)
    {
        return $this->userRepository->updateProfile();
    }
}
