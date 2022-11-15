<?php
namespace App\Repositories\Seller;

use App\Models\Country;
use App\Models\HowBuyPage;
use App\Models\Seller;
use App\Models\Shop;
use App\Repositories\Base\BaseRepository;


use Illuminate\Support\Facades\DB;


class  SellerRepository extends BaseRepository
{
    public $seller;
    public $shop;
    public function __construct(Seller $seller ,Shop $shop)
    {
        $this->seller=$seller;
        $this->shop=$shop;
    }
    public function getCountSeller(){

       $country=  DB::table('countries')->where('latitude','!=',null)->where('longitude','!=',null)->get();

       return $country;

    }
    public function getInfoSeller($limit,$id){
        if ($id != 0){
            $sellers= DB::table('sellers')->leftJoin('shops','sellers.id','=','shops.seller_id')
                ->where('shops.country_id',$id)
                ->select('sellers.id','sellers.email','sellers.phone',
                    'shops.address','shops.logo','shops.name','shops.facebook','shops.twitter','shops.google','shops.youtube','shops.country_id')
                ->orderBy('id', 'DESC')->paginate($limit);
        }
        else{
            $sellers= DB::table('sellers')->leftJoin('shops','sellers.id','=','shops.seller_id')

                ->select('sellers.id','sellers.email','sellers.phone',
                    'shops.address','shops.logo','shops.name','shops.facebook','shops.twitter','shops.google','shops.youtube','shops.country_id')
                ->orderBy('id', 'DESC')->paginate($limit);
        }

        return $sellers;

    }


}
