<?php
namespace App\Repositories\FeaturesPage;


use App\Models\FeaturesPage;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Facades\DB;


class FeaturesPageRepository extends BaseRepository
{
    public $photo;

    public function __construct(FeaturesPage $featuresPage)
    {
        $this->featuresPage=$featuresPage;
    }
    public function getAllFeatures(){
        return DB::table('features_pages')->get();
    }


}
