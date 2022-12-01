<?php

namespace App\Http\Resources;

use App\Models\AdditionalService;
use App\Models\Address;
use App\Models\DeliveryService;
use App\Models\Option;
use App\Models\OtherService;
use App\Models\ShipmentType;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "user" => User::find($this->user_id),
            "shipment_type" => ShipmentType::find($this->shipment_type_id),
            "sender_address" => Address::find($this->sender_address_id),
            "receiver_address" => Address::find($this->receiver_address_id),
            "shipment_options" => Option::whereIn("id",$this->shipment_option_id)->get(),
            "piece_count" => $this->piece_count,
            "order_type" => $this->order_type,
            "weight" => $this->weight ? $this->weight : null,
            "size" => $this->size ? $this->size : null,
            "additional_service" => AdditionalService::find($this->additional_service_id),
            "delivery_service" => DeliveryService::find($this->delivery_service_id),
            "other_service" => OtherService::find($this->other_service_id),
        ];
    }
}
