<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\SignUpVerificationEmail;
use App\Models\User;
use App\Notifications\VerifiyEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SignUpVerificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $verification_code = random_int(1000, 9999);
        $event->userData["verification_code"] = $verification_code;
        $user = User::Email($event->userData["email"])->first();
        DB::table('password_resets')->insert([
            'email' => $event->userData["email"],
            'token' => $verification_code,
            'created_at' => Carbon::now(),
        ]);
        $event->userData["id"] = $user->id;
        $user->notify(new VerifiyEmail($event->userData));
    }
}
