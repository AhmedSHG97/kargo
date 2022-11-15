<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShipmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use App\Http\Resources\ShipmentTypeResource;
use Exception;
use Illuminate\Support\Facades\Validator;

class ShipmentTypeController extends Controller
{

    public function __construct( ApiResponse $apiResponse, ShipmentType $shipmentTypeModel )
    {
        $this->apiResponse = $apiResponse;
        $this->shipmentTypeModel = $shipmentTypeModel;
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
        return $this->apiResponse->setSuccess("Type has been retrived successfully")->setData(ShipmentTypeResource::collection($this->shipmentTypeModel->all()))->getJsonResponse();
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
            "status" => "nullable|in:0,1", 
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        try{
            $type = $this->shipmentTypeModel->create($request->all());
            return $this->apiResponse->setSuccess("type has been added successfully")->setData(new ShipmentTypeResource($type))->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShipmentType  $shipmentType
     * @return \Illuminate\Http\Response
     */
    public function show(ShipmentType $shipment)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        return $this->apiResponse->setSuccess("data has been retreived successfully")->setData(new ShipmentTypeResource($shipment))->getJsonResponse();

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShipmentType  $shipmentType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShipmentType $shipment)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        $roles = [
            "name" => "nullable|string|min:1", 
            "status" => "nullable|in:0,1", 
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        try{
            $shipment->update($request->all());
            $shipment->refresh();
            return $this->apiResponse->setSuccess("type has been updated successfully")->setData(new ShipmentTypeResource($shipment))->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShipmentType  $shipmentType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShipmentType $shipment)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        try{
            $shipment->delete();
            return $this->apiResponse->setSuccess("type has been deleted successfully")->setData(new ShipmentTypeResource($shipment))->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }
}
