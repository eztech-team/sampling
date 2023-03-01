<?php

namespace App\Http\Traits;

use App\Mail\SendCodeMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

trait Message
{
    public function sendCodeToUserEmail($user): void
    {
        // $code = $this->generateFourDigitCode();
        $code = 1111;

        if (!$user->email_verification_send or Carbon::create($user->email_verification_send)
                ->addSeconds(60) <= Carbon::now()) {
            $user->update(['code' => $code]);

//            Mail::to($user->email)
//                ->send(new SendCodeMail($code))
//            ;

            $user->update(['email_verification_send' => Carbon::now()]);
        }
    }

    public function generateFourDigitCode()
    {
        return str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }
}
