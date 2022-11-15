<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Resources\OptionResource;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TypeOptionController extends Controller
{
    public function __construct( ApiResponse $apiResponse, Option $optionModel )
    {
        $this->apiResponse = $apiResponse;
        $this->optionModel = $optionModel;
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
        return $this->apiResponse->setSuccess("Option has been retrived successfully")->setData(OptionResource::collection($this->optionModel->all()))->getJsonResponse();
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
            "shipment_type_id" => "required|integer|exists:shipment_types,id", 
            "status" => "nullable|in:0,1", 
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        try{
            $option = $this->optionModel->create($request->all());
            return $this->apiResponse->setSuccess("Option has been added successfully")->setData(new OptionResource($option))->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Option  $option
     * @return \Illuminate\Http\Response
     */
    public function show(Option $option)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        return $this->apiResponse->setSuccess("data has been retreived successfully")->setData(new OptionResource($option))->getJsonResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Option  $option
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Option $option)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        $roles = [
            "name" => "nullable|string|min:1", 
            "status" => "nullable|in:0,1", 
            "shipment_type_id" => "nullable|integer|exists:shipment_types,id", 
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        try{
            $option->update($request->all());
            $option->refresh();
            return $this->apiResponse->setSuccess("option has been updated successfully")->setData(new OptionResource($option))->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Option  $option
     * @return \Illuminate\Http\Response
     */
    public function destroy(Option $option)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        try{
            $option->delete();
            return $this->apiResponse->setSuccess("type has been deleted successfully")->setData(new OptionResource($option))->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }
}
