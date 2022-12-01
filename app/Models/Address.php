<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Address extends Model
{
    use HasFactory;
    protected $table = "addresses";
    protected $fillable = [
        "address",
        "name",
        "phone",
        "mobile",
        "email",
        "mine",
        "user_id",
        "country_id",
        "state_id",
        "county_id",
        "shipping_address_id"
    ];

    public function getStateAttribute(){
        return DB::table("states")->find($this->state_id)->name;
    }

    public function getCountryAttribute(){
        return DB::table("countries")->find($this->country_id)->name;
    }

    public function getCountyAttribute(){
        return DB::table("counties")->find($this->county_id)->name;
    }
}
