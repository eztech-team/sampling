<?php

namespace App\Http\Controllers;

use App\Models\Aggregate;
use Illuminate\Http\Request;
use App\Http\Traits\CreateDeleteFiles;
use Spatie\SimpleExcel\SimpleExcelReader;

class AggregateController extends Controller
{
    use CreateDeleteFiles;

    public function index()
    {
        $this->authorize('aggregate-index');

        request()->validate([
            'project_id' => ['required', 'exists:projects,id']
        ]);

        $aggregate = Aggregate::where('project_id', request()->project_id);

        if(request()->amount_column){
            $aggregate = $aggregate->where('amount_column', '!=', null);
        }

        return response($aggregate->get(), 200);
    }

    public function store(Request $request)
    {
        $this->authorize('aggregate-create');

        $request->validate([
            'excel' => ['required'],
            'name' => ['required', 'max:255', 'unique:aggregates'],
            'amount_column' => ['nullable', 'integer'],
            'title' => ['required', 'boolean'],
            'project_id' => ['required', 'exists:projects,id']
        ]);

        $path = $this->storeFile('excel', 'excels', $request);

        $aggregate = Aggregate::create([
            'name' => $request->name,
            'path' => $path,
            'amount_column' => $request->amount_column,
            'title' => $request->title,
            'project_id' => $request->project_id
        ]);

        return response(['message' => 'Success', 'aggregate_id' => $aggregate->id], 200);
    }
//
//    public function storeTD(Request $request)
//    {
//        $request->validate([
//            'excels' => ['required', 'mimes:xls'],
//            'name' => ['required', 'max:255', 'unique:aggregates'],
//            'amount_column' => ['integer'],
//            'title' => ['required', 'boolean'],
//            'project_id' => ['required', 'exists:projects,id']
//        ]);
//
//        $aggregateIDs = [];
//
//        foreach ($request->excels as $row){
//            if ($request->hasFile($row)){
//
//                $path = $request->file($row)
//                    ->store('excels');
//
//                $aggregate = Aggregate::create([
//                    'name' => $request->name,
//                    'path' => $path,
//                    'amount_column' => $request->amount_column,
//                    'title' => $request->title,
//                    'project_id' => $request->project_id
//                ]);
//
//                $aggregateIDs = $aggregate->id;
//            }
//        }
//
//        return response([
//            'aggregate_ids' => $aggregateIDs,
//            'message' => 'Success'
//        ], 200);
//    }
}
