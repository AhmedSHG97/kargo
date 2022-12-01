<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DeliveryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DeliveryServiceController extends Controller
{

    public function __construct( ApiResponse $apiResponse, DeliveryService $deliveryServiceModel )
    {
        $this->apiResponse = $apiResponse;
        $this->deliveryServiceModel = $deliveryServiceModel;
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
        return $this->apiResponse->setSuccess("Delivery services has been retrived successfully")->setData($this->deliveryServiceModel->all())->getJsonResponse();

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
            $deliveryService = $this->deliveryServiceModel->create($request->all());
            return $this->apiResponse->setSuccess("Delivery service has been added successfully")->setData($deliveryService)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeliveryService  $service
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryService $service)
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
     * @param  \App\Models\DeliveryService  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliveryService $service)
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
            return $this->apiResponse->setSuccess("Delivery service has been updated successfully")->setData($service)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliveryService  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryService $service)
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
