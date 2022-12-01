<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    protected $fillable = [
        "shipment_type_id",
        "sender_address_id",
        "receiver_address_id",
        "shipment_option_id",
        "piece_count",
        "user_id",
        "order_type",
        "weight",
        "size",
        "additional_service_id",
        "delivery_service_id",
        "other_service_id",
        "integration_code"
    ];

    protected $casts = [
        "shipment_option_id" => "array"
    ];
    protected $attributes = [
        'shipment_option_id' => "[]"
    ];
}
