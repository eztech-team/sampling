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
        request()->validate([
            'project_id' => ['required', 'exists:projects,id']
        ]);

        return response(Aggregate::where('project_id', request()->project_id)->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'excel' => ['required', 'mimes:xls'],
            'name' => ['required', 'max:255', 'unique:aggregates'],
            'amount_column' => ['required', 'integer'],
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
}
