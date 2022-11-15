<?php
namespace App\Repositories\Job;


use App\Models\Career;
use App\Models\JobApplication;
use App\Models\MiddleBanner;
use App\Models\Photo;
use App\Repositories\Base\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class JobRepository extends BaseRepository
{
    public $photo;

    public function __construct(Career $career)
    {
        $this->career=$career;
    }


    public function getJobList(){
        return DB::table('careers')->get();
    }

    public function getJobApplication($jobApplicationRequest){
        $imageName = Carbon::now()->timestamp. '.' .  $jobApplicationRequest->file->extension();
        $jobApplicationRequest->file->storeAs('imgs/job/',$imageName, 's3');
        $file = env('AWS_BUCKET_URL') . 'imgs/job/'.$imageName;
        $jobAplication=JobApplication::create([
            'name'=>$jobApplicationRequest->name,
            'phone'=>$jobApplicationRequest->phone,
            'email'=>$jobApplicationRequest->email,
            'description'=>$jobApplicationRequest->description,
            'file'=>$file



        ]);

        return $jobAplication;
    }


}
