<?php

namespace App\Http\Controllers;

use App\Models\BalanceItem;
use App\Models\BalanceTest;
use Illuminate\Http\Request;

class BalanceTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $balanceItem = BalanceItem::where('project_id', request()->project_id)
            ->with('balanceTests')
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
        $request->validate([
            'name' => ['required', 'max:255'],
            'size' => ['required', 'integer'],
            'array_table' => ['required'],
            'aggregate_id' => ['required', 'exists:aggregates,id'],
            'deviation' => ['required', 'integer'],
            'effectiveness' => ['required', 'integer'],
            'nature_control_id' => ['required', 'exists:nature_controls,id'],
            'balance_item_id' => ['required', 'exists:balance_items,id'],
        ]);

        BalanceTest::create([
            'name' => $request->name,
            'size' => $request->size,
            'array_table' => $request->array_table,
            'aggregate_id' => $request->aggregate_id,
            'deviation' => $request->deviation,
            'effectiveness' => $request->effectiveness,
            'nature_control_id' => $request->nature_control_id,
            'balance_item_id' => $request->balance_item_id
        ]);

        return response(['message' => 'Success'], 200);
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
        $balanceTest->load(['balanceItem:id,name', 'aggregate:id,name', 'natureControl:id,name']);
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
