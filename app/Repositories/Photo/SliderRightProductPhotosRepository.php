<?php
namespace App\Repositories\Photo;


use App\Models\Photo;
use App\Models\Product;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Facades\DB;


class SliderRightProductPhotosRepository extends BaseRepository
{
    public $photo;

    public function __construct(Product $product)
    {
        $this->product=$product;
    }

    public function getSliderRightProductPhotos($id){
        return DB::table('products')->where('category_id',$id)->where('home',null)->get();
    }



}
