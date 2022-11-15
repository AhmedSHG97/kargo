<?php
namespace App\Repositories\ContactUs;

use App\Models\ContactUsPage;
use App\Repositories\Base\BaseRepository;


use Illuminate\Support\Facades\DB;


class ContactUsRepository extends BaseRepository
{
    public $photo;

    public function __construct(ContactUsPage $contactUsPage)
    {
        $this->contactUsPage=$contactUsPage;
    }
    public function getAllContactUsPage(){
        return DB::table('contact_us_pages')->get();
    }


}
