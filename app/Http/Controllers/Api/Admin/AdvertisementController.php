<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller
{

    public function __construct( ApiResponse $apiResponse, Advertisement $addsModel )
    {
        $this->apiResponse = $apiResponse;
        $this->addsModel = $addsModel;
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
        return $this->apiResponse->setSuccess("Adds has been retrived successfully")->setData($this->addsModel->all())->getJsonResponse();

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
            "discount" => "required|numeric|min:0", 
            "description" => "string|min:2", 
            "image" => "required|image|mimes:jpeg,jpg,png,gif|max:2048", 
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }

        try{
            $file = $request->image;
            $imageName = time() . uniqid() . "_" . strlen((string)auth()->id()) . "." . $file->getClientOriginalExtension();
            $image_path = Storage::disk("public")->putFileAs("uploads/advertisements", $file, $imageName);
            $advertisement = $this->addsModel->create(array_merge($request->except("image"),['image' => $image_path]));
            return $this->apiResponse->setSuccess("Additional service has been added successfully")->setData($advertisement)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Advertisement  $advertisement
     * @return \Illuminate\Http\Response
     */
    public function show(Advertisement $advertisement)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        return $this->apiResponse->setSuccess("data has been retreived successfully")->setData($advertisement)->getJsonResponse();

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Advertisement  $advertisement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        $roles = [
            "name" => "required|string|min:1", 
            "price" => "required|numeric|min:0", 
            "discount" => "required|numeric|min:0", 
            "description" => "string|min:2", 
            "image" => "nullable|image|mimes:jpeg,jpg,png,gif|max:2048"
        ];
        $validation = Validator::make($request->all(),$roles);
        if($validation->errors()->first()){
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        try{
            if($request->hasFile("image")){
                $file = $request->image;
                $imageName = time() . uniqid() . "_" . strlen((string)auth()->id()) . "." . $file->getClientOriginalExtension();
                $image_path = Storage::disk("public")->putFileAs("uploads/advertisements", $file, $imageName);
                $advertisement->update(array_merge($request->except("image"),['image' => $image_path]));
                $advertisement->refresh();
                return $this->apiResponse->setSuccess("advertisement has been updated successfully")->setData($advertisement)->getJsonResponse();
            }
            $advertisement->update($request->all());
            $advertisement->refresh();
            return $this->apiResponse->setSuccess("advertisement has been updated successfully")->setData($advertisement)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Advertisement  $advertisement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Advertisement $advertisement)
    {
        if(Auth::user()->role != "admin"){
            return $this->apiResponse->setError("You are not authorized")->setData()->getJsonResponse(401);
        }
        try{
            $advertisement->delete();
            if(file_exists($advertisement->image_path)){
                unlink($advertisement->image_path);
            }
            return $this->apiResponse->setSuccess("Service has been deleted successfully")->setData($advertisement)->getJsonResponse();
        }catch(Exception $exception){
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }
}
