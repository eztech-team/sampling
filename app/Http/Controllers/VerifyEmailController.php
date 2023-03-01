<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Http\Traits\Message;
use App\Mail\SendCodeMail;
use App\Models\User;
use App\Models\UserEmailCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class VerifyEmailController extends Controller
{

    use Message;

    public function verifyEmail(Request $request, AuthService $service)
    {
        $request->validate([
            'code'         => ['required', 'regex:/^\d{4}$/'],
            'email'        => ['email', 'required'],
        ]);

        $user = UserEmailCode::where('email', $request->email)
                    ->first()
        ;

        if ($user->code != (int)$request->code) {
            return response(['message' => 'Wrong code'], 502);
        }

        $isRegisteringByEmail = !$user->email_verified_at;

        if ($isRegisteringByEmail) {
            $createdUser = $service->createUser($user);

            $createdUser->update([
                'code' => null,
                'email_verified_at' => now(),
            ]);

            $user->delete();

            return response(
                [
                    'token' => $createdUser->createToken('API Token')->plainTextToken,
                ],
                200
            );
        } else {
            $passwordReset = DB::table('password_resets')
                               ->where('email', $user->email)
                               ->first()
            ;
            if ($request->resetToken != $passwordReset->token) {
                return response('Incorrect Token', 400);
            }

            $user->update(['code' => null]);

            $user->save();

            return response(
                [
                    'message' => 'reset password code is correct',
                    'email' => $user->email,
                    'resetToken' => $request->resetToken
                ],
                200
            );
        }
    }

    public function resendEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)
                    ->firstOrFail()
        ;

//        $code = $this->generateFourDigitCode();
        $code = 111111;
        $user->update(['code' => $code]);

        $user->save();

        if (!$user->email_verification_send or Carbon::create($user->email_verification_send)
                                                     ->addSeconds(60) >= Carbon::now()) {
            return response(['message' => 'You cant send message now wait 1 minute'], 418);
        }

//        Mail::to($user->email)
//            ->send(new SendCodeMail($code))
//        ;

        $user->update(['email_verification_send' => Carbon::now()]);

        return response(['message' => 'Resented code for email'], 200);
    }
}
