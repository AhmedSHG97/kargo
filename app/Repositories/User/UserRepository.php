<?php

namespace App\Repositories\User;

use App\Models\Category;
use App\Models\MiddleBanner;
use App\Models\Slider;
use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use App\Repositories\Base\BaseRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model, Client $client)
    {
        parent::__construct($model);
        $this->client = $client;
    }

    public function getUserByEmail(string $email)
    {
         return $this->model->email($email)->first();
    }
    public function findUnexpiredUser($id)
    {
        return $this->model->where("id", $id)->where("created_at", ">", Carbon::now()->subHours(1))->first();
    }

    public function uploadAvatar($file){
        $fileName = time() . uniqid() . "_" . strlen((string)auth()->id()) . "." . $file->getClientOriginalExtension();
        $file_path = Storage::disk("local")->putFileAs("users/avatar/", $file, $fileName);
        return "uploads/".$file_path;
    }


    public function getUserByToken($token)
    {
        $rememberToken = DB::table('password_resets')
            ->where('token', $token)
            ->where('created_at', '>',  Carbon::now()->subHours(1))
            ->first();
        if (empty($rememberToken)) {
            return null;
        }
        return $this->model->where("email", $rememberToken->email)->first();
    }


    public function getSliderAndBanner($type)
    {
        $middle_banners = [];
        if ( $type == 'banner' ){
            $middle_banners = MiddleBanner::orderBy('created_at', 'DESC')->get();
        } elseif ( $type == 'slider' ){
            $middle_banners = Slider::where('status', 1)->get();
        }

        return $middle_banners;
    }


    public function getAllCategory()
    {
        return Category::with(['subCategory', 'subSubCategory'])->get();
    }


    public function getAllBrands()
    {
        return DB::table('brands')->where('status', 1)->get();
    }
}
