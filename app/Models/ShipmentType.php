<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentType extends Model
{
    use HasFactory;
    protected $table = "shipment_types";
    protected $fillable = ["name", "status"];

    protected $hidden = ["created_at", "updated_at"];

    public function options(){
        return $this->hasMany(Option::class)->select("id","name","status");
    }
}
