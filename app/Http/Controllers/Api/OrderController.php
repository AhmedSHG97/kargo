<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\AdditionalService;
use App\Models\Address;
use App\Models\DeliveryService;
use App\Models\Order;
use App\Models\OtherService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct(ApiResponse $apiResponse, Order $order)
    {
        $this->orderModel = $order;
        $this->apiResponse = $apiResponse;
        $this->client = new Client([
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }
    public function getOrders()
    {
        $orders = $this->orderModel->where("user_id", Auth::id())->get();
        return $this->apiResponse->setSuccess("user orders retrieved successfully")->setData(OrderResource::collection($orders))->getJsonResponse();
    }

    public function DeleteOrder($order_id)
    {
        $order = $this->orderModel->where("user_id", Auth::id())->where("id", $order_id)->first();
        $order->delete();
        return $this->apiResponse->setSuccess("user order deleted successfully")->setData(new OrderResource($order))->getJsonResponse();
    }

    public function showOrder($order_id)
    {
        return $this->apiResponse->setSuccess("user order retreived successfully")->setData(new OrderResource($this->orderModel->find($order_id)))->getJsonResponse();
    }
    public function makeOrder(Request $request)
    {
        $roles = [
            "sender_address_id" => "required|integer|exists:addresses,id",
            "receiver_address_id" => "required|integer|exists:addresses,id",
            "shipment_type_id" => "required|integer|exists:shipment_types,id",
            "shipment_option_id" => "required|array",
            "shipment_option_id.*" => "required|integer|exists:options,id",
            "piece_count" => "required|numeric",
            "order_type" => "required|in:outgoing,incoming",
            "weight" => "nullable|numeric",
            "size" => "nullable|numeric",
            "additional_service_id" => "required|integer|exists:additional_services,id",
            "delivery_service_id" => "required|integer|exists:delivery_services,id",
            "other_service_id" => "required|integer|exists:other_services,id",
        ];
        $validation = Validator::make($request->all(), $roles);
        if ($validation->errors()->first()) {
            return $this->apiResponse->setError($validation->errors()->first())->setData()->getJsonResponse(400);
        }

        DB::beginTransaction();
        try {
            $integration_code = time() . Auth::id();
            $receiverAddress = Address::find($request->receiver_address_id);
            $senderAddress = Address::find($request->sender_address_id);
            $additionalService = AdditionalService::find($request->additional_service_id);
            $deliveryService = DeliveryService::find($request->delivery_service_id);
            $otherService = OtherService::find($request->other_service_id);
            $total_price = $additionalService->price + $deliveryService->price + $otherService->price;
            $order_data = $validation->validated();
            $order_data["shipment_option_id"] = $request->shipment_option_id;
            $order_data["integration_code"] = $integration_code;
            $order_data["user_id"] = Auth::id();
            // dd(array_merge($order_data, ['integration_code' => $integration_code]));
            $order = $this->orderModel->create($order_data);
            $data = [
                "CodeExpireDate" => "2024-09-16T16:53:28",
                "CollectionPrice" => $total_price,
                "ConfigurationId" => "EFC38C44F22A684F93403961D53E836E",
                "ExtProductList" =>
                [
                    "MpExtProductModel" => [
                        ["count" => 1, "SerivceCode" => 2]
                    ]
                ],
                "ExtServiceCodeList" => [
                    $additionalService->description
                ],
                "IntegrationCode" => $integration_code,
                "InvoiceNumber" => "1",
                "LovCollectionType" => 2,
                "LovPayOrType" => 3,
                "MainServiceCode" => "STNK",
                "PieceCount" => 1,
                "ReceiverAddressInfo" => [
                    "Address" => $receiverAddress->address,
                    "AddressId" => "",
                    "CityName" => $receiverAddress->state,
                    "Name" => $receiverAddress->name,
                    "PhoneNumber" => $receiverAddress->phone,
                    "MobilePhone" => $receiverAddress->phone,
                    "TaxNumber" => "123321",
                    "TownName" => $receiverAddress->county
                ],
                "SenderAddressInfo" => [
                    "Address" => $senderAddress->address,
                    "AddressId" => $senderAddress->shipping_address_id,
                    "CityName" => $senderAddress->state,
                    "Name" => $senderAddress->name,
                    "PhoneNumber" => $senderAddress->phone,
                    "TownName" => $senderAddress->county
                ],
                "TradingWaybillNumber" => "3792249",
            ];
            if(isset($request->weight) && !is_null($request->weight)){
                $data["weight"] = $request->weight;
            }
            if(isset($request->size) && !is_null($request->size)){
                $data["size"] = $request->size;
            }
            $response = $this->client->request('POST', "https://betashipping.bennebosmarket.online/api/shippment/create", [
                'body' => json_encode([
                    "model" => $data
                ]),
            ]);
            $result = json_decode($response->getBody(), true);
            if ($result["status"] == "faild") {
                return $this->apiResponse->setError($result->message)->setData()->getJsonResponse();
            }else{
                DB::commit();
                return $this->apiResponse->setSuccess("order created successfully")->setData(new OrderResource($order))->getJsonResponse();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }
}
