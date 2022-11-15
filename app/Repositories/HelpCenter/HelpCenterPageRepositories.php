<?php
namespace App\Repositories\HelpCenter;

use App\Models\HelpCenterPage;
use App\Models\HowBuyPage;
use App\Repositories\Base\BaseRepository;


use Illuminate\Support\Facades\DB;


class HelpCenterPageRepositories extends BaseRepository
{
    public $photo;

    public function __construct(HelpCenterPage $helpCenterPage)
    {
        $this->helpCenterPage=$helpCenterPage;
    }
    public function getAllHelp(){
        return DB::table('help_center_pages')->get();
    }


}
