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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $company->user = [
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname
        ];

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

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {
            if (Company::query()->where('id', '=', $id)->exists()) {
                Company::query()->where('id', '=', $id)->delete();
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return response(['message' => 'Bad'], 400);
        }
        return response(['message' => 'Success'], 200);
    }

    public function excel()
    {
        $companies = Company::query()
            ->select(['id', 'user_id', 'name', 'active'])
            ->whereNotNull('moderation_at')
            ->get()
            ->toArray();

        foreach ($companies as &$company) {
            $members_count = CompanyUser::query()
                ->where('company_id', $company['id'])
                ->count();
            $user = \App\Models\User::query()
                ->where('id', $company['user_id'])
                ->first();
            $city = City::query()->where('id', $user->city_id)->first();
            $country = Country::query()->where('id', $user->country_id)->first();
            $company['country_name'] = $country->name;
            $company['city_name']   = $city->name;
            $company['members_count'] = $members_count;
            $company['email'] = $user->email;
            $company['user_name'] = $user->name;
            $company['user_surname'] = $user->surname;
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Название');
        $sheet->setCellValue('C1', 'Статус');
        $sheet->setCellValue('D1', 'Страна');
        $sheet->setCellValue('E1', 'Город');
        $sheet->setCellValue('F1', 'Почта');
        $sheet->setCellValue('G1', 'Имя');
        $sheet->setCellValue('H1', 'Фамилия');
        $sheet->setCellValue('I1', 'Количество сотрудников');

        $row = 2;

        foreach ($companies as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $item['name']);
            $sheet->setCellValue('C' . $row, $item['active'] ? 'Активна' : 'Неактивна');
            $sheet->setCellValue('D' . $row, $item['country_name']);
            $sheet->setCellValue('E' . $row, $item['city_name']);
            $sheet->setCellValue('F' . $row, $item['email']);
            $sheet->setCellValue('G' . $row, $item['user_name']);
            $sheet->setCellValue('H' . $row, $item['user_surname']);
            $sheet->setCellValue('I' . $row, $item['members_count']);

            $row++;
        }

        $path   = "excels/companies.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return response(
            [
                'path' => env('APP_URL') . '/' .$path,
                'size' => $row - 1,
            ], 200
        );
    }
}
