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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $balanceItem = IncomeItem::where('project_id', request()->project_id)
            ->with('tests')
            ->get();

        return response($balanceItem, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Add comments

        if(!$request->income_id){
            $request->validate([
                'income_id' => 'nullable',
                'name' => ['required', 'max:255'],
                'size' => ['required', 'integer'],
                'array_table' => ['required'],
                'aggregate_id' => ['required', 'exists:aggregates,id'],
                'deviation' => ['required', 'integer'],
                'effectiveness' => ['required', 'integer'],
                'nature_control_id' => ['required', 'exists:nature_controls,id'],
                'balance_item_id' => ['required', 'exists:balance_items,id'],
            ]);

            $incomeTest = IncomeTest::create([
                'name' => $request->name,
                'first_size' => $request->size,
                'array_table' => $request->array_table,
                'aggregate_id' => $request->aggregate_id,
                'deviation' => $request->deviation,
                'effectiveness' => $request->effectiveness,
                'nature_control_id' => $request->nature_control_id,
                'balance_item_id' => $request->balance_item_id
            ]);

            $aggregate = Aggregate::find($request->aggregate_id);

            //        $pathToCsv = $this->storeFile('excel', 'excels', $request);

            Excel::import(new ExcelImport($request->size, [], $incomeTest->id, $aggregate->amount_column), $aggregate->path);
            //check logic
            /*
             * if check logics error 0 status = 1
             * else status = 0
             * */
        }

        if($request->income_id){
            $incomeTest = IncomeTest::find($request->balance_id);
            $request->validate([
                'size' => ['required', 'integer'],
                'nature_control_id' => [
                    'exists:nature_controls,id',
                    new NatureControlRule($incomeTest->nature_control_id, $incomeTest->id)]
            ]);

            $balanceTestExcel = IncomeTestExcel::where('income_test_id', $incomeTest->id)->first()->data;

            $ignore = array_column($balanceTestExcel, 'row');

            Excel::import(new ExcelImport($request->size, $ignore, $incomeTest->id, $incomeTest->aggregate_id), 'excels/bZsiNCQyu1iL9yu5a4KyfYZcf4SpYcmdG2Y3tThz.xls');

            $incomeTest->update([
                'second_size' => $request->size,
            ]);
        }

        return response(['message' => 'Success', 'income_id' => $incomeTest->id], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BalanceTest  $balanceTest
     * @return \Illuminate\Http\Response
     */
    public function show(IncomeTest $incomeTest)
    {
        //Add comments

        $incomeTest->load(['excel']);
        return response($incomeTest, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BalanceTest  $balanceTest
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomeTest $incomeTest)
    {
        $incomeTest->forceDelete();

        return response(['message' => 'Success'], 200);
    }
}
