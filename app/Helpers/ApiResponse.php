<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use illuminate\Http\JsonResponse;
use Illuminate\Routing\ResponseFactory;

class ApiResponse 
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var array
     */
    protected $body;
    public function __construct(ResponseFactory $jsonResponse)
    {  
        $this->response = $jsonResponse;
    }

    public function setData($data = null) : object
    {
        $this->body['data'] = $data;
        return $this;
    }

    public function setSuccess($message): object
    {
        $this->body['message'] = $message;
        $this->body['status'] = true;
        return $this;
    }
    public function setError($message): object
    {
        $this->body['message'] = $message;
        $this->body['status'] = false;
        return $this;
    }

    public function getJsonResponse($responseCode = null): JsonResponse
    {
        $responseCode = $responseCode ? $responseCode :  200;
        if(isset($this->body["status"]) &&  $this->body["status"] == false && is_null($responseCode)){
            $responseCode = 400;
        }
        return $this->response->json($this->body,$responseCode,[],JSON_NUMERIC_CHECK);
    }
    
}
