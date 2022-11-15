<?php
/**
 * created by: tushar Khan
 * email : tushar.khan0122@gmail.com
 * date : 6/29/2022
 */


namespace App\Repositories\WishList;

use App\Http\Requests\WishListRequest;
use App\Models\WishList;
use App\Repositories\Base\BaseRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class WishListRepository extends BaseRepository implements WishListRepositoryInterface
{
    public function __construct(WishList $model)
    {
        parent::__construct($model);
    }
    public function addToWishList(WishListRequest $wishListRequest)
    {
        $insertData = $this->getRequestData($wishListRequest);

        $checkIfExist = WishList::where($insertData)->first();

        if ($checkIfExist) {
            return $checkIfExist;
        }

        if( $wishListRequest->has('heart') ) $insertData['heart'] = $wishListRequest->heart;
        $insertData['created_at'] = date('Y-m-d H:i:s');

        return WishList::create($insertData);
    }


    public function getWishList(WishListRequest $wishListRequest)
    {
        $insertData = $this->getRequestData($wishListRequest);

        return WishList::where($insertData)->get();
    }


    public function updateWishList(WishListRequest $wishListRequest)
    {
        $insertData = [
            'product_id' => $wishListRequest->product_id,
            'device_token' => $wishListRequest->device_token,
        ];

        $checkIfExist = WishList::where($insertData);

        if ($checkIfExist->count() <= 0) {
            return false;
        }

        if( $wishListRequest->has('heart') ) $insertData['heart'] = $wishListRequest->heart;
        if( $wishListRequest->has('user_id') ) $insertData['user_id'] = $wishListRequest->user_id;
        $checkIfExist->update($insertData);

        return $checkIfExist->first();
    }


    public function deleteWishList(WishListRequest $wishListRequest)
    {
        if($wishListRequest->has("user_id")){
            return $this->model->where('user_id',$wishListRequest->user_id)->delete();
        }else{
            return $this->model->where('device_token',$wishListRequest->device_token)->delete();
        }

    }

    public function deleteWishListProduct(WishListRequest $wishListRequest)
    {
        if($wishListRequest->has("user_id")){
            return $this->model->where('user_id',$wishListRequest->user_id)
                ->where('product_id',$wishListRequest->product_id)
                ->delete();
        }else{
            return $this->model->where('device_token',$wishListRequest->device_token)->where('product_id',$wishListRequest->product_id)->delete();
        }

    }

    private function  getRequestData(FormRequest $formRequest){
        $insertData = [
            'product_id' => $formRequest->product_id,
            'device_token' => $formRequest->device_token,
        ];

        if( $formRequest->has('user_id') ) $insertData['user_id'] = $formRequest->user_id;

        return $insertData;
    }
    public function assignUser($device_token){
        $this->model
        ->where('device_token',$device_token)
        ->update(['user_id'=>Auth::id()]);

    }

}
