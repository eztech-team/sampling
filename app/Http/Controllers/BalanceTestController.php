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

        if(!$request->balance_test_id){
            $request->validate([
                'balance_test_id' => 'nullable',
                'name' => ['required', 'max:255'],
                'size' => ['required', 'integer'],
                'array_table' => ['required'],
                'aggregate_id' => ['required', 'exists:aggregates,id'],
                'deviation' => ['required', 'string'],
                'effectiveness' => ['required', 'string'],
                'nature_control_id' => ['required', 'exists:nature_controls,id'],
                'balance_item_id' => ['required', 'exists:balance_items,id'],
                'method' => ['required', 'boolean']
            ]);

            $balanceTest = BalanceTest::create([
                'name' => $request->name,
                'first_size' => $request->size,
                'array_table' => $request->array_table,
                'aggregate_id' => $request->aggregate_id,
                'deviation' => $request->deviation,
                'effectiveness' => $request->effectiveness,
                'nature_control_id' => $request->nature_control_id,
                'balance_item_id' => $request->balance_item_id,
                'method' => $request->method,
            ]);

            if($balanceTest->first_size){
                $aggregate = Aggregate::find($request->aggregate_id);
                $ignore = [];
                if($aggregate->title){
                    $ignore = [1];
                }

                Excel::import(
                    new ExcelImport(
                        random: $request->size,
                        ignore: $ignore,
                        method: $request->method,
                        balanceTestId: $balanceTest->id),
                    $aggregate->path
                );
            }
        }

        if($request->balance_test_id){
            $balanceTest = BalanceTest::find($request->balance_test_id);
            $request->validate([
                'size' => ['required', 'integer'],
                'nature_control_id' => [
                    'exists:nature_controls,id',
                    new NatureControlRule(natureControlID: $balanceTest->nature_control_id, balanceID: $balanceTest->id)]
            ]);
            $balanceTest->update([
                'second_size' => $request->size,
            ]);

            if($balanceTest->second_size){
                $aggregate = Aggregate::find($balanceTest->aggregate_id);
                $balanceTestExcel = BalanceTestExcel::where('balance_test_id', $balanceTest->id)->first()->data;

                if ($aggregate->title){
                    $balanceTestExcel[] = ["row" => 1];
                }

                $ignore = array_column($balanceTestExcel, 'row');

                Excel::import(
                    new ExcelImport(
                        random: $request->size,
                        ignore: $ignore,
                        method: $balanceTest->method,
                        balanceTestId: $balanceTest->id), $aggregate->path);
            }
        }

        return response(['message' => 'Success', 'balance_test_id' => $balanceTest->id], 200);
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

        $balanceTest = $balanceTest->load(['aggregate', 'natureControl']);

        return response($balanceTest,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BalanceTest  $balanceTest
     * @return \Illuminate\Http\Response
     */
    public function destroy(BalanceTest $balanceTest)
    {
        $balanceTest->forceDelete();

        return response(['message' => 'Success'], 200);
    }

    public function excel(BalanceTest $balanceTest)
    {
        $balanceTestExcel = BalanceTestExcel::where('balance_test_id', $balanceTest->id)
            ->select('id as balance_test_excel_id')
            ->get();

        return response(
            [
                'excels' => $balanceTestExcel,
                'first_size' => $balanceTest->first_size,
                'second_size' => $balanceTest->second_size
            ],
            200);
    }
}
