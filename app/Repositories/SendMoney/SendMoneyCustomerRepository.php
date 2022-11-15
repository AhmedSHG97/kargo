<?php
namespace App\Repositories\SendMoney;


use App\Models\Photo;
use App\Models\SendMoneyCustomer;
use App\Models\User;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Product\PhotosRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;


class SendMoneyCustomerRepository extends BaseRepository
{
    public $photo;

    public function __construct(SendMoneyCustomer $sendMoneyCustomer)
    {
        $this->sendMoneyCustomer=$sendMoneyCustomer;
    }
    public function getCustomerPoint($id){
        return DB::table('send_money_customers')->where('customer_id',$id)->get();
    }


}
