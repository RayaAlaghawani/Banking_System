<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Http\Requests\EmployeeRequest;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\userRepository;
use Illuminate\Support\Facades\Auth;

class EmployeeService
{
    protected $repository;

    public function __construct(userRepository $repository)
    {
        $this->repository = $repository;
    }

    public function add_employee(EmployeeRequest $request)
    {
        if (optional(Auth::user())->hasRole('admin')) {
            $employee = $this->repository->create($request->all());
            $employeeRole = $this->repository->getByName('employee');
            $employee->assignRole($employeeRole);
            if ($request->has('permissions')) {
              //  $employeeRole->syncPermissions($request->permissions);
                $employee->syncPermissions($request->permissions);
            }
            $employee->load('permissions', 'roles');
            $employee = $this->repository->getById($employee->id);
            $employee = $this->appendRolesAndPermission($employee);
            $message = 'Employee added successfully';
            $code = 200;
        }else{
            $employee = null;
            $message = 'You do not have permission to perform this action';
            $code = 403;

        }

        return [
            'user' => $employee,
            'message' => $message,
            'code' => $code
        ];
    }
    private function appendRolesAndPermission($user)
    {

        $roles = [];
        foreach ($user->roles as $role) {
            $roles[] = $role->name;

        }
        unset($user['roles']);
        $user['roles'] = $roles;
        $permissions = [];
        foreach ($user->permissions as $permission) {
            $permissions[] = $permission->name;
        }
        unset($user['permissions']);
        $user['permissions'] = $permissions;
        return $user;







    }










}


