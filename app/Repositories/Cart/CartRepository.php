<?php

/**
 * created by: tushar Khan
 * email : tushar.khan0122@gmail.com
 * date : 6/22/2022
 */


namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function addToCart(FormRequest $formRequest)
    {
        $userId = null;
        $condition   = [
            'product_id' => $formRequest->input('product_id'),
        ];

        $ip = $formRequest->has('ip_address') ? $formRequest->input('ip_address') : "";

        if (strlen($ip)  == 0) $condition = ['user_id' => $formRequest->has('user_id') ? $formRequest->input('user_id') : null];
        else $condition['ip_address'] = $ip;

        $product = Product::find($formRequest->product_id);
        $condition['color'] = $product->color_id;
        $condition['size'] = $product->size_id;

        $cart = DB::table('carts')
            ->where($condition);

        $price = $product->unit_price;
        $quantity = $formRequest->input('quantity');
        $discount = $formRequest->has('discount') ? $formRequest->input('discount') : 0;


        if ($formRequest->has('tax'))
            $insertData['tax'] = $formRequest->input('tax');

        if ($formRequest->has('shipping_cost'))
            $insertData['shipping_cost'] = $formRequest->input('shipping_cost');

        if ($formRequest->has('coupon_code'))
            $insertData['coupon_code'] = $formRequest->input('coupon_code');

        if ($cart->count() > 0) {
            $cartObject = $cart->first();
            $cartId = $cartObject->id;
            $cartPrice = $cartObject->price ?? 0;
            $cartQuantity = $cartObject->quantity ?? 1;
            $cartDiscount = $cartObject->discount ?? 0;

            $insertData['price'] = ($price * $quantity) + $cartPrice;
            $insertData['quantity'] = $quantity + $cartQuantity;
            $insertData['discount'] = $cartDiscount + (($price * $discount) / 100) * $quantity;
            $insertData['updated_at'] = date('Y-m-d');
            $insertData['owner_id'] = $product->user_id;
            $insertData['user_id'] = $formRequest->has('user_id') ? $formRequest->input('user_id') : null;

            try {
                $cart->update($insertData);

                return Cart::with(['product_size', 'product_color'])->find($cartId);
            } catch (\Exception $exception) {
                return false;
            }
        } else {

            $extra['price'] = $price * $quantity;
            $extra['quantity'] = $quantity;
            $extra['discount'] = (($price * $discount) / 100) * $quantity;;
            $extra['status'] = 0;
            $extra['color'] = $product->color_id;
            $extra['size'] = $product->size_id;
            $extra['owner_id'] = $product->user_id;
            $extra['user_id'] = $formRequest->has('user_id') ? $formRequest->input('user_id') : null;
            
            $extra['created_at'] = date('Y-m-d');
            
            $insertData = $this->processInsertData($formRequest, $extra);

            try {
                $cart = Cart::create($insertData);
                return $cart;
            } catch (\Exception $exception) {
                return false;
            }
        }
    }


    public function addToCarts(FormRequest $formRequest)
    {
        $productIds = $formRequest->input('product_id');
        $userIds = $formRequest->input('user_id');
        $ownerIds = $formRequest->input('owner_id');
        $addressIds = $formRequest->input('address_id');
        $prices = $formRequest->input('price');
        $taxs = $formRequest->input('tax');
        $shippingCosts = $formRequest->input('shipping_cost');
        $discounts = $formRequest->input('discount');
        $couponCodes = $formRequest->input('coupon_code');
        $quantities = $formRequest->input('quantity');
        $colors = $formRequest->input('color');
        $sizes = $formRequest->input('size');
        $statuss = $formRequest->input('status');
        $ipAddress = $formRequest->has('ip_address') ? $formRequest->input('ip_address') : $formRequest->ip();
        $responseData = [];

        foreach ($productIds as $ind => $productId) {
            $condition   = [
                'product_id' => $productId,
            ];

            $ip = ($formRequest->has('user_id')) ? $formRequest->input('ip_address') : $formRequest->ip();

            if (isset($userIds[$ind]) && !empty($userIds[$ind])) $condition = ['user_id' => $userIds[$ind]];
            else $condition['ip_address'] = $ip;

            if (isset($colors[$ind]) && !empty($colors[$ind])) $condition['color'] = $colors[$ind];
            if (isset($sizes[$ind]) && !empty($sizes[$ind])) $condition['size'] = $sizes[$ind];

            $cart = DB::table('carts')
                ->where($condition);

            $price = $prices[$ind];
            $quantity = $quantities[$ind];
            $discount = $discounts[$ind] ?? 0;

            $insertData = [];

            if (isset($statuss[$ind]) && !empty($statuss[$ind]))
                $insertData['status'] = $statuss[$ind];

            if (isset($taxs[$ind]) && !empty($taxs[$ind]))
                $insertData['tax'] = $taxs[$ind];

            if (isset($shippingCosts[$ind]) && !empty($shippingCosts[$ind]))
                $insertData['shipping_cost'] = $shippingCosts[$ind];

            if (isset($couponCodes[$ind]) && !empty($couponCodes[$ind]))
                $insertData['coupon_code'] = $couponCodes[$ind];

            if ($cart->count() > 0) {
                $cartId = $cart->first()->id;
                $cartPrice = $cart->first()->price ?? 0;
                $cartQuantity = $cart->first()->quantity ?? 1;
                $cartDiscount = $cart->first()->discount ?? 0;

                $insertData['price'] = ($price * $quantity) + $cartPrice;
                $insertData['quantity'] = $quantity + $cartQuantity;
                $insertData['discount'] = $cartDiscount + (($price * $discount) / 100) * $quantity;
                $insertData['updated_at'] = date('Y-m-d');
                $insertData['ip_address'] = $ipAddress;

                try {
                    $cart->update($insertData);

                    $responseData[] = $cartId;
                } catch (\Exception $exception) {
                    return false;
                }
            } else {
                $insertData['price'] = $price * $quantity;
                $insertData['quantity'] = $quantity;
                $insertData['discount'] = (($price * $discount) / 100) * $quantity;;
                $insertData['status'] = 0;
                $insertData['created_at'] = date('Y-m-d');

                $insertData['owner_id'] = $ownerIds[$ind];
                $insertData['product_id'] = $productIds[$ind];
                if (isset($userIds[$ind]) && !empty($userIds[$ind]))
                    $insertData['user_id'] = $userIds[$ind];
                $insertData['ip_address'] = $ip;

                if (isset($colors[$ind]) && !empty($colors[$ind]))
                    $insertData['color'] = $colors[$ind];

                if (isset($sizes[$ind]) && !empty($sizes[$ind]))
                    $insertData['size'] = $sizes[$ind];

                $insertData['ip_address'] = $ipAddress;

                try {
                    $id  = DB::table('carts')->insertGetId($insertData);
                    $responseData[] = $id;
                } catch (\Exception $exception) {
                    return false;
                }
            }
        }

        return Cart::whereIn('id', $responseData)->get();
    }


    private function processInsertData(FormRequest $formRequest, $extra = null)
    {
        $insertData = [];
        $insertData['owner_id'] = $formRequest->input('owner_id');
        $insertData['product_id'] = $formRequest->input('product_id');

        if ($formRequest->has('user_id'))
            $insertData['user_id'] = $formRequest->input('user_id');
        if ($formRequest->has('ip_address'))
            $insertData['ip_address'] = $formRequest->input('ip_address');

        /*if ( $formRequest->has('color') )
            $insertData['color'] = $formRequest->input('color');

        if ( $formRequest->has('size') )
            $insertData['size'] = $formRequest->input('size');*/

        if (is_array($extra)) $insertData = array_merge($insertData, $extra);

        $insertData['status'] = 0;

        return $insertData;
    }

    public function removeCart(FormRequest $formRequest)
    {
        $condition = [];

        if (auth()->user()) {
            $condition['user_id'] = Auth::id();
        }
        if ($formRequest->has('ip_address')) $condition['ip_address'] = $formRequest->input('ip_address');

        if (count($condition) > 0) {
            DB::table('carts')->where($condition)->delete();
        }
    }


    public function removeProduct(FormRequest $formRequest)
    {
        DB::table('carts')->where("id",$formRequest->input('cart_id'))->delete();
    }


    public function addUserToCart(Request $request)
    {
        $ip_address = $request->has('ip_address') ? $request->input('ip_address') : $request->ip();

        $updateData = [];

        if (Auth::check()) {
            $updateData['user_id'] = Auth::id();
        }

        $cart = Cart::where([
            'ip_address' => $ip_address
        ]);

        if ($cart->count() > 0) {
            $cart->update($updateData);
        }

        return $cart->get();
    }


    public function getCartByUser(Request $request)
    {
        if ($request->has('user_id')) {
            return Cart::where("user_id", $request->user_id)->get();
        } else if ($request->has('ip_address')) {
            return Cart::where("ip_address", $request->input('ip_address'))->get();
        }


        
    }
}
