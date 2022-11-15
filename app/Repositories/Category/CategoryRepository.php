<?php

namespace App\Repositories\Category;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Category\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use PDO;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function parentCategories():Collection{
        return $this->model->where('parent_id',"0")->get();
    }
    public function subCategories($category_id, $limit){
        $subCategories = Category::where("parent_id", $category_id)->where('sub_parent_id',0)->paginate($limit);
        return $subCategories;
    }
    public function subSubCategories($category_id, $limit){
        $subSubCategories = Category::where('sub_parent_id',$category_id)->paginate($limit);
        return $subSubCategories;
    }
    public function getSomeCategoryStatisitcs($limit, $category_id){
        $products_data = [];

        $categories = [$category_id];
        $subcategories = DB::table('categories')->where('parent_id', $category_id)->where('sub_parent_id', 0)->pluck("id")->toArray();
        $categories = array_merge($categories, $subcategories);
        $subsubcategories = DB::table('categories')->whereIn('sub_parent_id', $subcategories)->pluck("id")->toArray();
        $categories = array_merge($categories, $subsubcategories);

        $products_db =  Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo");
        //deals of the day
        $products_data['deals_of_day'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('products.deal_of_day', 1)
        ->orderBy('products.id', 'DESC')
        ->get());
        $products_data['new_arrivals'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('products.new_arrival', 1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['best_selling'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('best_selling',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['top_ranked'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('top_ranked',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['dropshipping'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('dropshipping',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['opportunity_products'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('true_view',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['best_deals'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('best_big_deal',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['new_arrivals'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_deal_new_arrival',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['most_viewed'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_deal_most_viewed',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['deal_of_season'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('deal_of_season',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['big_needs'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_needs',1)
        ->orderBy('products.id', 'DESC')
        ->get());

        $products_data['big_deals']['big_quantity'] = (Product::leftJoin("sellers","products.user_id", "=","sellers.id")
        ->where('products.status',1)
        ->whereIn('category_id',$categories)
        ->limit($limit)
        ->select("products.slug", "products.gallery_image", "products.name","products.thumbnail","products.unit_price","products.id", "products.total_review", "products.avg_review", "sellers.name as seller_name", "sellers.avatar as seller_logo")->where('big_quantity',1)
        ->orderBy('products.id', 'DESC')
        ->get());
        return $products_data;
    }


    public function getAllHeaderCategory(){
        return $this->model->where('parent_id', 0)->where('sub_parent_id', 0)->take(7)->get();
    }

    public function getAllHeaderSubCategory($id){
        return $this->model->where('id',$id)->where('parent_id', 0)->where('sub_parent_id', 0)->get();
    }
}
