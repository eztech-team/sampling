<?php

namespace App\Http\Controllers;

use App\Imports\ExcelImport;
use App\Http\Traits\CreateDeleteFiles;
use App\Models\Aggregate;
use App\Models\IncomeItem;
use App\Models\IncomeTest;
use App\Models\IncomeTestExcel;
use App\Rules\NatureControlRule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IncomeTestController extends Controller
{
    use CreateDeleteFiles;

    public function index()
    {
        $balanceItem = IncomeItem::where('project_id', request()->project_id)
            ->with('tests')
            ->get();

        return response($balanceItem, 200);
    }

    public function store(Request $request)
    {
        //Add comments

        if(!$request->income_test_id){
            $request->validate([
                'income_test_id' => 'nullable',
                'name' => ['required', 'max:255'],
                'size' => ['required', 'integer'],
                'array_table' => ['required'],
                'aggregate_id' => ['required', 'exists:aggregates,id'],
                'deviation' => ['required', 'integer'],
                'effectiveness' => ['required', 'integer'],
                'nature_control_id' => ['required', 'exists:nature_controls,id'],
                'income_item_id' => ['required', 'exists:income_items,id'],
                'method' => ['required', 'boolean'],
            ]);

            $incomeTest = IncomeTest::create([
                'name' => $request->name,
                'first_size' => $request->size,
                'array_table' => $request->array_table,
                'aggregate_id' => $request->aggregate_id,
                'deviation' => $request->deviation,
                'effectiveness' => $request->effectiveness,
                'nature_control_id' => $request->nature_control_id,
                'income_item_id' => $request->income_item_id,
                'method' => $request->method,
            ]);

            $aggregate = Aggregate::find($request->aggregate_id);
            $ignore = [];
            if($aggregate->title){
                $ignore = [1];
            }

            //        $pathToCsv = $this->storeFile('excel', 'excels', $request);

            Excel::import(new ExcelImport(random: $request->size, ignore: $ignore, method: $request->method, incomeTestID: $incomeTest->id), $aggregate->path);
        }

        if($request->income_test_id){
            $incomeTest = IncomeTest::find($request->balance_id);
            $request->validate([
                'size' => ['required', 'integer'],
                'nature_control_id' => [
                    'exists:nature_controls,id',
                    new NatureControlRule($incomeTest->nature_control_id, $incomeTest->id)]
            ]);

            $aggregate = Aggregate::find($incomeTest->aggregate_id);
            $incomeTestExcel = IncomeTestExcel::where('income_test_id', $incomeTest->id)->first()->data;

            if ($aggregate->title){
                $incomeTestExcel[] = ["row" => 1];
            }

            $ignore = array_column($incomeTestExcel, 'row');

            Excel::import(
                new ExcelImport(
                    random: $request->size,
                    ignore: $ignore,
                    method: $incomeTest->method,
                    incomeTestID: $incomeTest->id), $aggregate->path);

            $incomeTest->update([
                'second_size' => $request->size,
            ]);
        }

        return response(['message' => 'Success', 'income_test_id' => $incomeTest->id], 200);
    }

    public function show(IncomeTest $incomeTest)
    {
        //Add comments

        $incomeTest = $incomeTest->load(['aggregate', 'natureControl']);

        return response($incomeTest,200);
    }

    public function destroy(IncomeTest $incomeTest)
    {
        $incomeTest->forceDelete();

        return response(['message' => 'Success'], 200);
    }

    public function excel(IncomeTest $incomeTest)
    {
        //Add comments

        $incomeTestExcel = IncomeTestExcel::where('income_test_id', $incomeTest->id)
            ->select('id as income_test_excel_id')
            ->get();

        return response(
            [
                'excels' => $incomeTestExcel,
                'first_size' => $incomeTest->first_size,
                'second_size' => $incomeTest->second_size
            ],
            200);
    }
}
