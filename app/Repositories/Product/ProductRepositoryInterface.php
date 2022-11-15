<?php

namespace App\Repositories\Product;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    public function getDiscountProducts($limit) : LengthAwarePaginator;
}
