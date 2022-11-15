<?php

namespace App\Repositories\Category;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface CategoryRepositoryInterface
{
    public function parentCategories():Collection;
}
