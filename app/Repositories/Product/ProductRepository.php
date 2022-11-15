<?php

namespace App\Repositories\Product;


use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Models\Shop;
use App\Models\Size;
use Illuminate\Support\Str;
use App\Repositories\Base\BaseRepository;
use Exception;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    private $main_product_id;
    public function __construct(
        Product $model,
        Seller $sellerModel,
        Shop $shopModel,
        SellerWallet $sellerWalletModel,
        Brand $brandModel,
        Category $categoryModel,
        Color $colorModel,
        Size $sizeModel,
        ProductDetail $ProductDetailsModel,
    ) {
        $this->sellerModel = $sellerModel;
        $this->sellerWalletModel = $sellerWalletModel;
        $this->shopModel = $shopModel;
        $this->brandModel = $brandModel;
        $this->categoryModel = $categoryModel;
        $this->colorModel = $colorModel;
        $this->sizeModel = $sizeModel;
        $this->ProductDetailsModel = $ProductDetailsModel;

        parent::__construct($model);
    }

    public function getDiscountProducts($limit = 10): LengthAwarePaginator
    {
        return $this->model->withCount('wishlists')
            //->translate()
            ->where('products.discount_date_from', "<", now())
            ->where('products.discount_date_to', ">", now())
            ->where('products.discount', ">", 0)
            //->selectAll()
            ->paginate($limit);
    }

    public function getSingleProduct($productId)
    {
        $product = Product::find($productId, ['size_id','color_id','main_product_id']);
        return $this->productBySizeOrColor($productId, $product->size_id, $product->color_id);
    }


    public function productBySizeOrColor($productId, $sizeId, $colorId)
    {

        $mainProduct = Product::find($productId);

        $product = Product::with(['category', 'brand', 'reviews'])
            ->where('size_id', $sizeId)
            ->where('main_product_id', $mainProduct->main_product_id);



        if ($colorId)
            $product = $product->where('color_id', $colorId);

        $product = $product->first();

        if (empty($product)) {
            $mainProduct->load(['category', 'brand', 'reviews']);
            return $mainProduct;
        }

        $commonColors = Product::where('main_product_id', $product->main_product_id)
            ->where('size_id', $sizeId)
            ->pluck('color_id')
            ->toArray();


        $commonSizes = Product::where('main_product_id', $product->main_product_id)
            ->pluck('size_id')
            ->toArray();


        $commonTypes = Product::where('main_product_id', $product->main_product_id)
            ->pluck('type_id')
            ->toArray();

        $commonSizes = Size::whereIn('id', $commonSizes)->get(['size','id']);

        $statuses=Size::whereIn('type_id',$commonTypes)->get(['size','id']);

        $commonColors = Color::whereIn('id', $commonColors)->get(['name','image','id']);


        $product->setAttribute('commonColors', $commonColors);
        $product->setAttribute('commonSizes', $commonSizes);
        $product->setAttribute('statuses', $statuses);

        return  $product;
    }

    public function productByCategorySlug($slug, ProductRequest $productRequest)
    {
        $products = Product::with(['category'])
            ->withCount('wishlists')
            ->whereHas('category', function ($query) use ($slug) {
                $query->where('slug', $slug);
            });
        return $this->sortProduct($productRequest, $products);
    }

    public function getCategoryProducts($category_id, $limit){
        $categories = [$category_id];

        $subcategories = DB::table('categories')->where('parent_id', $category_id)->pluck("id")->toArray();
        $categories = array_merge($categories, $subcategories);
        $subsubcategories = DB::table('categories')->whereIn('sub_parent_id', $categories)->pluck("id")->toArray();
        $categories = array_merge($categories, $subsubcategories);

        $products = Product::whereIn('category_id', $categories)->where('status', 1)->paginate($limit);
        return $products;
    }


    private function sortProduct(FormRequest $formRequest, $collection)
    {
        if ($formRequest->sortByPrice == 'low_to_high') {
            $collection = $collection->orderBy('unit_price', 'ASC');
        }
        if ($formRequest->sortByPrice == 'high_to_low') {
            $collection = $collection->orderBy('unit_price', 'DESC');
        }
        if ($formRequest->has('sortByBrand')) {
            $collection = $collection->where('brand_id', $formRequest->sortByBrand);
        }
        if ($formRequest->has('orderByMinOrder')) {
            $collection = $collection->where('min_qty', $formRequest->orderByMinOrder);
        }
        if ($formRequest->has('sortByReview ')) {
            $collection = $collection->where('avg_review', $formRequest->sortByReview);
        }
        if ($formRequest->has('sortByMinPrice')  && $formRequest->has('sortByMaxPrice')) {
            $collection = $collection->where('unit_price', '>=', $formRequest->sortByMinPrice)
                ->where('unit_price', '<=', $formRequest->sortByMaxPrice);
            //            $collection = $collection->whereBetween('unit_price', [$formRequest->sortByMinPrice, $formRequest->sortByMaxPrice]);
        }
        if ($formRequest->has('sortByCategory')) {
            $collection = $collection->where('category_id', $formRequest->sortByCategory);
        }
        if ($formRequest->has('sQuery')) {
            $collection = $collection->where('name', 'like', '%' . $formRequest->sQuery . '%');
        }

        return $collection->get();
    }

    public function addProductsSellers($sellers)
    {
        foreach ($sellers as $key => $seller) {

            $seller_name = array_keys($seller)[0];
            $seller_exists = $this->sellerModel->where('name', Str::lower($seller_name))->count();
            if (!$seller_exists) {
                // Save seller data
                $addedSeller = $this->saveSeller($seller, $seller_name);

                //save seller shop
                $addedShop = $this->saveSellerShop($addedSeller);

                //add Seller wallet
                $addedSellerWallet = $this->saveSellerWallet($addedSeller->id);
            }
        }
    }

    private function saveSeller($seller, $seller_name)
    {
        // // $image_path = "seller/";
        // // $image_name = uniqid() . '.png';
        // try {
        //     if ($seller_name != '') {
        //         // if ($seller[$seller_name]['logo'] != '') {
        //         //     Storage::disk('local')->put($image_path . $image_name, file_get_contents($seller[$seller_name]['logo']));
        //         // }
        //     }
        // } catch (Exception $exception) {
        // }

        $data = [
            'name' => Str::lower($seller_name),
            "avatar" => $seller[$seller_name]['logo'],
            'email' => Str::lower(str_replace(' ', '_', $seller_name)) . '@bennebosmarket.com',
            "password" => Hash::make('bmarket1234')
        ];
        $addedSeller = $this->sellerModel->create($data);
        return $addedSeller;
    }

    private function saveSellerShop($seller)
    {
        $data = [
            "seller_id" => $seller->id,
            "verification_status" => 1,
            "name" => Str::lower($seller->name),
            "slug" => $seller->name . $seller->id,
            "logo" => $seller->avatar,
            "address" => 'turkey',
            "category_id" => 0,
        ];
        return $this->shopModel->create($data);
    }
    private function saveSellerWallet($seller_id)
    {
        $addedSellerWallet = $this->sellerWalletModel->create(['seller_id' => $seller_id]);
        return $addedSellerWallet;
    }

    public function addProducts($products)
    {
        foreach ($products as $product) {
            $prouct_key = array_keys($product)[0];
            $brand_cateogries = [];
            foreach ($product[$prouct_key] as $key => $productData) {
                if (count($productData['categories']) >= 4) {
                    $brand = $this->brandModel->where('name', $productData['brand'])->first();
                    if (!$brand) {
                        $brand = $this->brandModel->create(['name' => $productData['brand'], 'slug'  => Str::slug($productData['brand'])]);
                    }
                    if (!is_null($brand->category_id)) {
                        $brand_cateogries = json_decode($brand->category_id);
                    }
                    $main_category = $this->categoryModel->where('name', Str::lower($productData['categories'][1]))->first();
                    if (!$main_category) {
                        $main_category = $this->categoryModel->create([
                            'parent_id' => 0,
                            'sub_parent_id' => 0,
                            'name' => Str::lower($productData['categories'][1]),
                            'slug' => Str::slug($productData['categories'][1]),
                            'commision_rate' => 0,
                            'banner' => "default_category.png",
                        ]);
                    }
                    $sub_category = $this->categoryModel->where('name', Str::lower($productData['categories'][2]))->where('parent_id', $main_category->id)->first();
                    if (!$sub_category) {
                        $sub_category = $this->categoryModel->create([
                            'parent_id' => $main_category->id,
                            'sub_parent_id' => 0,
                            'name' => Str::lower($productData['categories'][2]),
                            'slug' => Str::slug($main_category->name . " " . $productData['categories'][2]),
                            'commision_rate' => 0,
                            'banner' => "default_category.png",
                        ]);
                    }
                    $sub_sub_category = $this->categoryModel->where('name', Str::lower(Arr::last($productData['categories'])))->where("sub_parent_id", $sub_category->id)->first();
                    if (!$sub_sub_category) {
                        $sub_sub_category =
                        $this->categoryModel->create([
                            'parent_id' => $main_category->id,
                            'sub_parent_id' => $sub_category->id,
                            'name' => Str::lower(Arr::last($productData['categories'])),
                            'slug' => Str::slug($main_category->name . " " . Arr::last($productData['categories'])),
                            'commision_rate' => 0,
                            'banner' => "default_category.png",
                        ]);
                    }
                    if($brand_cateogries){
                        if (!in_array($sub_sub_category->id, $brand_cateogries)) {
                            array_push($brand_cateogries, $sub_sub_category->id);
                            $brand->update(['category_id' => json_encode($brand_cateogries)]);
                            $brand->refresh();
                        }
                    }

                    if (isset($productData['color'])) {
                        $color_name = array_keys($productData['color'])[0];
                        $color = $this->colorModel->create(
                            [
                                'name' => Str::lower($color_name),
                                // 'image' => $this->saveProductDetailsThumbnail($productData['color'][$color_name])
                                'image' => $productData['color'][$color_name]
                                ]
                            );
                        } else {
                            $color = null;
                        }
                        if (isset($productData['size'])) {
                            $size = $this->sizeModel->where('size', $productData['size'])->first();
                            if (!$size) {
                                $size = $this->sizeModel->create(['size' => $productData['size']]);
                            }
                        } else {
                            $size = null;
                        }
                        $seller_id = $this->sellerModel->where('name', Str::lower($productData['seller']))->first(['id'])->id;
                        if (isset($productData['images'])) {
                            // $images = $this->saveProductDetailsImages($productData['images']);
                            $images = json_encode($productData['images']);
                        } else {
                            $images = json_encode([]);
                        }
                        // $thumbnail = $this->saveProductDetailsThumbnail($productData['thumbnail']);
                        if( isset($productData['images']) && count($productData['images']) > 0 && $productData['images'][0] != ""){
                            $thumbnail = $productData['images'][0];
                        }else{
                            $thumbnail = $productData['thumbnail'];
                        }
                        if((isset($productData['images']) && count($productData['images']) > 0 && $productData['images'][0] != "") && !str_contains($productData['images'][0],"legal-requirement-card-new-white.png") && !str_contains($productData['images'][0],"seller-selection-stamp-v14.png") && !str_contains($productData['images'][0],"indexing-sticker-stamp/moon/aa7816f3-395f-43b0-a9fc-0b806f923a6a.png")){
                            $status = 0;
                        }else{
                            $status = 1;
                        }
                        if ($key == 0) {
                            $productDetails = $this->model->create([
                                "name" => Str::lower($productData['title']),
                                "slug" => Str::slug(Str::lower($productData['title'])) . "-" . uniqid(),
                                "title" => Str::lower($productData['title']),
                                "description" => $productData['description'],
                                "category_id" => $sub_sub_category->id,
                                "size_id" => $size ? $size->id : null,
                                "brand_id" => $brand ? $brand->id : null,
                                "color_id" => $color ? $color->id : null,
                                "user_id" => $seller_id,
                                "gallery_image" => $images,
                                "thumbnail" => $thumbnail,
                                "status" => $status,
                                "min_qty" => 1,
                                "quantity" => 20,
                                "unit_price" => (float)$productData['price'],
                            ]);
                            $productDetails->update(['main_product_id' => $productDetails->id]);
                            $productDetails->refresh();
                            $this->main_product_id = $productDetails->id;
                        }
                        if ($key != 0) {
                            $productDetails = $this->model->create([
                                "name" => Str::lower($productData['title']),
                                "slug" => Str::slug(Str::lower($productData['title'])) . "-" . uniqid(),
                                "title" => Str::lower($productData['title']),
                                "description" => $productData['description'],
                                "category_id" => $sub_sub_category->id,
                                "size_id" => $size ? $size->id : null,
                                "brand_id" => $brand ? $brand->id : null,
                                "color_id" => $color ? $color->id : null,
                                "user_id" => $seller_id,
                                "main_product_id" => $this->main_product_id,
                                "gallery_image" => $images,
                                "thumbnail" => $thumbnail,
                                "status" => $status,
                                "min_qty" => 1,
                                "quantity" => 20,
                                "unit_price" => (float)$productData['price'],
                            ]);
                        }
                    }
                }
            }
        }

    public function saveProductDetailsImages($images)
    {
        $image_names = [];
        foreach ($images as $image) {
            $image_path = "product/";
            $image_name = uniqid() . '.png';
            Storage::disk('local')->put($image_path . $image_name, file_get_contents($image));
            $image_names[] = $image_name;
        }
        return json_encode($image_names);
    }
    public function saveProductDetailsThumbnail($thumbnail)
    {
        $image_path = "product/";
        $image_name = uniqid() . '.png';
        Storage::disk('local')->put($image_path . $image_name, file_get_contents($thumbnail));
        return $image_name;
    }

    public function getSomeStatisitcsProducts($limit){
        $products_data = [];
        $products_db =  Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo");
        //deals of the day
        $products_data['deals_of_day'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('products.deal_of_day', 1)
        ->orderBy('products.id', 'DESC')
        ->get());
        $products_data['new_arrivals'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('products.new_arrival', 1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['best_selling'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('best_selling',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['top_ranked'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('top_ranked',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['dropshipping'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('dropshipping',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['opportunity_products'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('true_view',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['best_deals'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('best_big_deal',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['new_arrivals'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_deal_new_arrival',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['most_viewed'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_deal_most_viewed',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['deal_of_season'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('deal_of_season',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['big_needs'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_needs',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['big_quantity'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_quantity',1)
        ->orderBy('products.id', 'DESC')
        ->get());
        return $products_data;
    }

    public function getFullThumbnail($products){
        foreach($products as $key => $product){
            $products[$key]->thumbnail = url("uploads/product") . "/" . $product->thumbnail;
        }
        return $products;
    }
    public function filterProducts($filter_queries, $limit){
        $fillables = $this->model->getFillable();
        $fillables = array_merge($fillables,["min_price", "max_price"]);
        $query = $this->model;
        foreach($filter_queries  as $key => $value){
            if(in_array($key, $fillables)){
                if($key == "category_id"){
                    $categories = [$value];
                    $subcategories = DB::table('categories')->where('parent_id', $value)->pluck("id")->toArray();
                    $categories = array_merge($categories, $subcategories);
                    $subsubcategories = DB::table('categories')->whereIn('sub_parent_id', $categories)->pluck("id")->toArray();
                    $categories = array_merge($categories, $subsubcategories);
                    $query = $query->whereIn("category_id",$categories);

                }elseif($key == "min_price"){
                    $query = $query->where('unit_price', ">=" ,$value);
                }elseif($key == "max_price"){
                    $query = $query->where('unit_price', "<=" ,$value);
                }else{
                    $query = $query->where($key,$value);
                }
            }
        }
        return $query->paginate($limit);
    }
}
