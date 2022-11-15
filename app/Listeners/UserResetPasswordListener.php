<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Mail;
use App\Mail\UserResetPasswordMail;
use App\Notifications\ResetPassword;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UserResetPasswordListener
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
        $user = $event->user;
        $token = random_int(1000, 9999);
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);
        $user->token = $token;
        $event->user->notify(new ResetPassword($user));
    }
}
