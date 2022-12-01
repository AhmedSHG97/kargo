<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OtherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OtherServiceController extends Controller
{

    public function __construct( ApiResponse $apiResponse, OtherService $otherServiceModel )
    {
        $this->apiResponse = $apiResponse;
        $this->otherServiceModel = $otherServiceModel;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        return $this->apiResponse->setSuccess("services has been retrived successfully")->setData($this->otherServiceModel->all())->getJsonResponse();

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        $roles = [
            "name" => "required|string|min:1", 
            "price" => "required|numeric|min:0", 
            "description" => "string|min:2", 
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        try{
            $otherService = $this->otherServiceModel->create($request->all());
            return $this->apiResponse->setSuccess("service has been added successfully")->setData($otherService)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OtherService  $service
     * @return \Illuminate\Http\Response
     */
    public function show(OtherService $service)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        return $this->apiResponse->setSuccess("data has been retreived successfully")->setData($service)->getJsonResponse();

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OtherService  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OtherService $service)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        $roles = [
            "name" => "required|string|min:1", 
            "description" => "required|min:2", 
            "price" => "required|numeric|min:0", 
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        try{
            $service->update($request->all());
            $service->refresh();
            return $this->apiResponse->setSuccess("service has been updated successfully")->setData($service)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OtherService  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherService $service)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        try{
            $service->delete();
            return $this->apiResponse->setSuccess("Service has been deleted successfully")->setData($service)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }
}
