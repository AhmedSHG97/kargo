<?php
namespace App\Repositories\HowBuyPage;

use App\Models\HowBuyPage;
use App\Repositories\Base\BaseRepository;


use Illuminate\Support\Facades\DB;


class  HowBuyPageRepository extends BaseRepository
{
    public $photo;

    public function __construct(HowBuyPage $howBuyPage)
    {
        $this->howBuyPage=$howBuyPage;
    }
    public function getAllHowBuyPage(){
        return DB::table('how_buy_pages')->get();
    }


}
