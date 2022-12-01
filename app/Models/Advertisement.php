<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $table = "advertisements";

    protected $fillable = [
        "name",
        "description",
        "price",
        "discount",
        "image"
    ];

    public function getImageAttribute(){
        return url("storage/" . $this->attributes['image']) ;
    }
    public function getImagePathAttribute(){
        return "storage/" . $this->attributes['image'];
    }
}
