<?php
namespace App\Repositories\Photo;


use App\Models\Photo;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Facades\DB;


class PhotosRepository extends BaseRepository
{
    public $photo;

    public function __construct(Photo $photo)
    {
       $this->photo=$photo;
    }

    public function getCategoryPhoto($id){
        return DB::table('photos')->where('category_id',$id)->where('home',null)->get();
    }

    public function getSliderRightProductPhoto($id){
        return DB::table('products')->where('category_id',$id)->orderBy('created_at','desc')->take(6)->get(['id','thumbnail','name']);
    }
    public function getHomePhoto(){
        return DB::table('photos')->where('category_id',null)->where('home','home')->get();
    }


}
