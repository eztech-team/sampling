<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    public function getTeamsAndUsers(Request $request)
    {
        $teams = Team::whereHas('company', function ($q){
            $q->where('id', auth('sanctum')->id());
        })
            ->with(['users:id,name,surname,email'])
            ->select('id', 'name')
        ;

        $companyID = User::companyID();

        $users = User::whereHas('company', function ($q) use($companyID){
            $q->where('companies.id', $companyID)
            ;
        })->select('id as user_id', 'name', 'surname');

        $filter = $request->filter;

        if($filter){
                $users = $users->where(function ($query) use ($filter) {
                    $query->where('name', 'ilike', "%$filter%")
                        ->orWhere('surname', 'ilike', "%$filter%")
                        ->orWhere('email', 'ilike', "%$filter%");
                });
                $teams = $teams->where(function ($query) use ($filter){
                   $query->where('name', 'ilike', "%$filter%");
                });
        }

        return response([
            'teams' => $teams->get(),
            'users' => $users->get()
        ], 200);
    }
}
