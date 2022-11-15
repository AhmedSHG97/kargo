<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $table = "options";
    protected $fillable = ["name", "status", "shipment_type_id"];
    protected $hidden = ["created_at", "updated_at"];

    public function type(){
        return $this->belongsTo(ShipmentType::class,"shipment_type_id");
    }
}
