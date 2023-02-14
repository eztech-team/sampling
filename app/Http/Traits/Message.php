<?php

namespace App\Http\Traits;

use App\Mail\SendCodeMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

trait Message
{
    public function sendCodeToUserEmail($user, $message)
    {
        // $code = $this->generateFourDigitCode();
        $code = '111111';

        $user->update(['code' => $code]);

        $validation_message = 'wait';
        if (!$user->email_verification_send or Carbon::create($user->email_verification_send)
                                                     ->addSeconds(60) >= Carbon::now()) {

            Mail::to($user->email)
                ->send(new SendCodeMail($code))
            ;

            $user->update(['email_verification_send' => Carbon::now()]);
        }

        return response(['message' => $message, 'email' => $user->email], 200);
    }

    public function generateFourDigitCode()
    {
        $code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        return $code;
    }

    public function getMessage($user, $code)
    {
        if (!$user->phone_verified_at) {
            $message = "$code - код для регистрации";
        } else {
            $message = "$code - код для смены пароля";
        }

        return $message;
    }
}
