<?php

namespace App\Http\Controllers;

use App\Jobs\SendMailJob;
use App\Mail\UserCreateMail;
use App\Models\CompanyUser;
use App\Models\CreateUserMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('user-index');

        $users = User::whereHas('company', function ($q){
            $q->where('user_id', auth('sanctum')->id())->get();
        });

        return response($users, 200);
    }

    public function sendNotification(Request $request)
    {
        $this->authorize('user-store');

        $request->validate([
            'emails' => ['required', 'array'],
        ]);

        $companyID = CompanyUser::where('user_id', auth('sanctum')->id())->first()->company_id;
        foreach ($request->emails as $email){
            $token = Str::uuid();

            $createUser = CreateUserMail::create([
                'email' => $email,
                'token' => $token,
                'company_id' => $companyID,
            ]);

            SendMailJob::dispatch($createUser->email, $createUser->token);
        }

        return response(['message' => 'Message sent successfully'], 200);
    }

    public function createUser(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'exists:create_user_mails,email', 'unique:users'],
            'token' => ['required'],
            'name' => ['required', 'max:255'],
            'surname' => ['required', 'max:255'],
            'password' => ['required', 'max:20'],
            'conf_password' => ['required', 'same:password'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id'=> ['required', 'exists:cities,id'],
        ]);

        $userMail = CreateUserMail::where('token', $request->token)
            ->where('email', $request->email)->first();

        if($userMail){
            $data['role_id'] = Role::USER;
            $data['password'] = Hash::make($data['password']);

            $createdUser = User::create($data);
            $createdUser->company()->attach($userMail->company_id);
            $createdUser->update([
                'email_verification_send' => now(),
                'email_verified_at' => now(),
            ]);
            $userMail->delete();

            return response(['token' => $createdUser->createToken('API Token')->plainTextToken]);
        }

        return response(['message' => 'Token or email incorrect'], 403);
    }

    public function users()
    {
        $this->authorize('user-edit');

        $users = $this->checkUser();

        $users = $this->filter(request()->filter, $users)
            ->with('role')
            ->get();

        return response($users, 200);
    }

    public function addUserToProjectsAndTeam(Request $request)
    {
        $this->authorize('user-store');

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'projects' => ['nullable'],
            'teams' => ['nullable'],
            'projects.*.project_id' => ['required_if:projects,!=,null']
        ]);

        $user = $this->checkUser();

        $user = $user->where('id', $request->user_id)
            ->first();

        if($request->projects){
            $user->projects()->sync($request->projects, false);
        }
        if($request->teams){
            $user->teams()->sync($request->teams, false);
        }

        return response(['message' => 'Success'], 200);
    }

    private function checkUser()
    {
        $companyID = CompanyUser::where('user_id', auth('sanctum')->id())->first()->company_id;

        return User::whereHas('company', function ($q) use($companyID){
            $q->where('id', $companyID);
        })->select('id', 'name', 'surname', 'email', 'role_id');
    }

    private function filter($filter, $user){
        if($filter){
            return $user->where('name', 'ilike', "%$filter%")
                ->orWhere('surname', 'ilike' ,"%$filter%")
                ->orWhere('email', 'ilike' ,"%$filter%");
        }

        return $user;
    }
}
