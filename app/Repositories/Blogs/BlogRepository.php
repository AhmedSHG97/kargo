<?php
namespace App\Repositories\Blogs;

use App\Http\Controllers\Api\BolgsController;
use App\Models\BlogCategory;
use App\Models\NewsPage;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Facades\DB;

class BlogRepository extends BaseRepository
{
    public $photo;

    public function __construct(BlogCategory $blogCategory)
    {
        $this->blogCategory=$blogCategory;
    }
    public function getAllBlogs($limit){
        return DB::table('blogs')->latest()->paginate($limit);;
    }


}
