<?php

namespace App\Http\Controllers;
use App\Helpers\ResponseHelper;
use App\Http\Requests\EmployeeRequest;
use App\Services\EmployeeService;
use App\Services\TestService;
use Illuminate\Http\Request;
class EmployeeController extends Controller
{
    private $service;

    public function __construct(
        EmployeeService $service
    ) {
        $this->service = $service;
    }
    public function index()
    {

    }
    /**
     * Show the form for creating a new resource.
     */
    public function add_employee(EmployeeRequest $request)
    {
        $data=[];
        try{
            $data=$this->service->add_employee($request);
            return ResponseHelper::Success($data['user'],$data['message']);}
        catch( \Throwable $th)
        {
            $message=$th->getMessage();
            return ResponseHelper::Error($data,$message);}
    }



}
