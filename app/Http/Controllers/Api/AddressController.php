<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Address;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function __construct(Address $addressModel, ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
        $this->addressModel = $addressModel;
        $this->client = new Client([
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);;
    }
    public function getAddresses()
    {
        return $this->apiResponse->setSuccess("user addresses retrieved successfully")->setData($this->addressModel->where("user_id", Auth::id())->get())->getJsonResponse();
    }


    public function Create(Request $request)
    {
        $roles = [
            "name" => "required|string|min:1",
            "address" => "required|string|min:1",
            "phone" => "required|string|min:10",
            "mobile" => "nullable|string|min:10",
            "email" => "required|email|min:4",
            "mine" => "required|in:0,1",
            "country_id" => "required|integer|exists:countries,id",
            "state_id" => "required|integer|exists:states,id",
            "county_id" => "required|integer|exists:counties,id",

        ];
        $validation = Validator::make($request->all(), $roles);
        if ($validation->errors()->first()) {
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        $state = DB::table("states")->find($request->state_id);
        $county = DB::table("counties")->find($request->county_id);
        $address_data = [
            "CompleteAddress" => $request->address,
            "Name" => $request->name,
            "PhoneNumber" => $request->phone,
            "EMail" => $request->email,
            "CustomerAddressId" => time() . Auth::id(),
            "CityName" => $state->name,
            "TownName" => $county->name,
            "AccountId" => "{913CA874-370A-13DC-AFA4-B94E7CCD14B3}",
            "CustomerAddressInfoId" => "{913CA874-370A-13DC-AFA4-B94E7CCD14B3}",
        ];
        $response = $this->client->request("POST", "https://betashipping.bennebosmarket.online/api/shippment/save/address", [
            "body" => json_encode([
                "address" => $address_data
            ]),
        ]);
        $arasAddress = json_decode($response->getBody()->getContents());
        if ($arasAddress->status != "success") {
            return $this->apiResponse->setError($arasAddress->message)->setData()->getJsonResponse();
        }
        $arasAddressID = $arasAddress->data->AddressId;
        $address = $this->addressModel->create(array_merge($request->all(), ["shipping_address_id" => $arasAddressID, "user_id" => Auth::id()]));

        return $this->apiResponse->setSuccess("Address has been saved successfully")->setData($address)->getJsonResponse();
    }

    public function update(Request $request)
    {
        $roles = [
            "address_id" => "required|integer|exists:addresses,id",
            "name" => "required|string|min:1",
            "address" => "required|string|min:1",
            "phone" => "required|string|min:10",
            "mobile" => "nullable|string|min:10",
            "email" => "required|email|min:4",
            "mine" => "required|in:0,1",
            "country_id" => "required|integer|exists:countries,id",
            "state_id" => "required|integer|exists:states,id",
            "county_id" => "required|integer|exists:counties,id",

        ];
        $validation = Validator::make($request->all(), $roles);
        if ($validation->errors()->first()) {
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse();
        }
        $state = DB::table("states")->find($request->state_id);
        $county = DB::table("counties")->find($request->county_id);
        $address_data = [
            "CompleteAddress" => $request->address,
            "Name" => $request->name,
            "PhoneNumber" => $request->phone,
            "EMail" => $request->email,
            "CustomerAddressId" => time() . Auth::id(),
            "CityName" => $state->name,
            "TownName" => $county->name,
            "AccountId" => "{913CA874-370A-13DC-AFA4-B94E7CCD14B3}",
            "CustomerAddressInfoId" => "{913CA874-370A-13DC-AFA4-B94E7CCD14B3}",
        ];
        $response = $this->client->request("POST", "https://betashipping.bennebosmarket.online/api/shippment/save/address", [
            "body" => json_encode([
                "address" => $address_data

            ]),
        ]);
        $arasAddress = json_decode($response->getBody()->getContents());
        if ($arasAddress->status != "success") {
            return $this->apiResponse->setError($arasAddress->message)->setData()->getJsonResponse();
        }
        $arasAddressID = $arasAddress->data->AddressId;
        $this->addressModel->find($request->address_id)->update(array_merge($request->all(), ["shipping_address_id" => $arasAddressID, "user_id" => Auth::id()]));
        $address = $this->addressModel->find($request->address_id);
        return $this->apiResponse->setSuccess("Address has been saved successfully")->setData($address)->getJsonResponse();
    }

    public function show($address_id)
    {
        $address = $this->addressModel->find($address_id);
        return $this->apiResponse->setSuccess("Address has been retrieved successfully")->setData($address)->getJsonResponse();
    }

    public function destroy($address_id)
    {
        $address = $this->addressModel->find($address_id);
        $address->delete();
        return $this->apiResponse->setSuccess("Address has been deleted successfully")->setData($address)->getJsonResponse();
    }
}
