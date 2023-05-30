<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Country;
use App\Models\Project;
use App\Models\Role;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function show()
    {
        $this->authorize('company-show');

        $company = Company::whereHas('users', function ($user){
            $user->where('id', auth('sanctum')->id());
        })->first();

        return response($company, 200);
    }

    public function index(Request $request)
    {
        $companies = Company::query()
            ->select(['id', 'user_id', 'name'])
            ->whereNotNull('moderation_at');

        if ($request->is_accept) {
            $companies = $companies
                ->where('active', '=', $request->is_accept);
        }
        $companies = $companies->paginate();

        foreach ($companies as $company) {
            $members_count = CompanyUser::query()
                ->where('company_id', $company->id)
                ->count();
            $user = \App\Models\User::query()
                ->where('id', $company->user_id)
                ->first();
            $city = City::query()->where('id', $user->city_id)->first();
            $country = Country::query()->where('id', $user->country_id)->first();
            $company->country_name = $country->name;
            $company->city_name    = $city->name;
            $company->members_count = $members_count;
            $company->email = $user->email;
        }

        return response($companies, 200);
    }

    public function showCompany(int $id)
    {
        $company = Company::query()
            ->where('id', $id)
            ->select(['id', 'user_id', 'name'])
            ->first();
        $members_count = CompanyUser::query()
            ->where('company_id', $company->id)
            ->count();
        $project_count = Project::query()->where('company_id', $company->id)->count();

        $user = \App\Models\User::query()
            ->where('id', $company->user_id)
            ->first();
        $city = City::query()->where('id', $user->city_id)->first();
        $country = Country::query()->where('id', $user->country_id)->first();

        $members_ids =  CompanyUser::query()
                            ->where('company_id', $company->id)
                            ->select(['user_id'])
                            ->get()
                            ->pluck('user_id')
            ->toArray();
        $members = \App\Models\User::query()
            ->whereIn('id', $members_ids)
            ->select(['id', 'name', 'surname', 'email'])
            ->get();

        foreach ($members as $member) {
            $project_member_count = DB::table('project_user')
                ->where('user_id', '=', $member->id)
                ->count();
            $member->project_count = $project_member_count;
        }
        $company->members = $members;
        $company->country_name = $country->name;
        $company->city_name = $city->name;
        $company->members_count = $members_count;
        $company->email = $user->email;
        $company->project_count = $project_count;


        return response($company, 200);
    }

    public function requestList(Request $request)
    {
        $companies = Company::query()
            ->select(['id', 'user_id', 'name', 'created_at'])
            ->whereNull('moderation_at');

        if ($request->is_accept) {
            $companies = $companies
                ->where('active', '=', $request->is_accept);
        }
        $companies = $companies->paginate();

        foreach ($companies as $company) {
            $members_count = CompanyUser::query()
                ->where('company_id', $company->id)
                ->count();
            $user = \App\Models\User::query()
                ->where('id', $company->user_id)
                ->first();
            $city = City::query()->where('id', $user->city_id)->first();
            $country = Country::query()->where('id', $user->country_id)->first();
            $company->country_name = $country->name;
            $company->city_name    = $city->name;
            $company->members_count = $members_count;
            $company->email = $user->email;
            $company->user = [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname
            ];
            unset($company->user_id);
        }

        return response($companies, 200);
    }
}
