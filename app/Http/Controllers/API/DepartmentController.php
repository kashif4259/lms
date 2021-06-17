<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Repositories\DepartmentRepository;
use App\Http\Controllers\API\BaseController as BaseController;

class DepartmentController extends BaseController
{
    protected $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        parent::__construct();

        $this->departmentRepository = $departmentRepository;
    }

    public function index()
    {
        return $this->departmentRepository->getDepartments();
    }

    public function create()
    {
        return $this->departmentRepository->createDepartment();
    }

    public function update($id)
    {
        return $this->departmentRepository->modify($id);
    }

    public function destroy($id)
    {
        return $this->departmentRepository->destroy($id);
    }
}
