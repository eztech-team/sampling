<?php

namespace App\Http\Controllers;

use App\Imports\ExcelImport;
use App\Http\Traits\CreateDeleteFiles;
use App\Models\Aggregate;
use App\Models\BalanceItem;
use App\Models\BalanceTest;
use App\Models\BalanceTestExcel;
use App\Rules\NatureControlRule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BalanceTestController extends Controller
{
    use CreateDeleteFiles;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $balanceItem = BalanceItem::where('project_id', request()->project_id)
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

        if(!$request->balance_id){
            $request->validate([
                'balance_id' => 'nullable',
                'name' => ['required', 'max:255'],
                'size' => ['required', 'integer'],
                'array_table' => ['required'],
                'aggregate_id' => ['required', 'exists:aggregates,id'],
                'deviation' => ['required', 'integer'],
                'effectiveness' => ['required', 'integer'],
                'nature_control_id' => ['required', 'exists:nature_controls,id'],
                'balance_item_id' => ['required', 'exists:balance_items,id'],
            ]);

            $balanceTest = BalanceTest::create([
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

            Excel::import(new ExcelImport($request->size, [], $balanceTest->id, $aggregate->amount_column), 'excels/bZsiNCQyu1iL9yu5a4KyfYZcf4SpYcmdG2Y3tThz.xls');
            //check logic
            /*
             * if check logics error 0 status = 1
             * else status = 0
             * */
        }

        if($request->balance_id){
            $balanceTest = BalanceTest::find($request->balance_id);
            $request->validate([
                'size' => ['required', 'integer'],
                'nature_control_id' => [
                    'exists:nature_controls,id',
                    new NatureControlRule($balanceTest->nature_control_id, $balanceTest->id)]
            ]);

            $balanceTestExcel = BalanceTestExcel::where('balance_test_id', $balanceTest->id)->first()->data;
            $ignore = array_column($balanceTestExcel, 'row');

            Excel::import(new ExcelImport($request->size, $ignore, $balanceTest->id, $balanceTest->aggregate_id), 'excels/bZsiNCQyu1iL9yu5a4KyfYZcf4SpYcmdG2Y3tThz.xls');

            $balanceTest->update([
                'second_size' => $request->size,
            ]);
        }

        return response(['message' => 'Success', 'balance_id' => $balanceTest->id], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BalanceTest  $balanceTest
     * @return \Illuminate\Http\Response
     */
    public function show(BalanceTest $balanceTest)
    {
        //Add comments

        $balanceTest->load(['excel']);
        return response($balanceTest, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BalanceTest  $balanceTest
     * @return \Illuminate\Http\Response
     */
    public function destroy(BalanceTest $balanceTest)
    {
        $balanceTest->delete();

        return response(['message' => 'Success'], 200);
    }
}
