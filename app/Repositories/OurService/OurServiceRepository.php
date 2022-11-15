<?php
namespace App\Repositories\OurService;


use App\Models\OurServicePage;
use App\Repositories\Base\BaseRepository;


use Illuminate\Support\Facades\DB;


class OurServiceRepository extends BaseRepository
{
    public $photo;

    public function __construct(OurServicePage $ourServicePage)
    {
        $this->ourServicePage=$ourServicePage;
    }
    public function getAllServices(){
        return DB::table('our_service_pages')->get();
    }


}
