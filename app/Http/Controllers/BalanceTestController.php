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
        $this->authorize('test-index');

        $balanceItem = BalanceItem::where('project_id', request()->project_id)
            ->with('tests')
            ->get();
        foreach ($balanceItem as $item) {
            foreach ($item->tests as $test) {
                if (!is_null($test->faq)) {
                    $test->faq = json_decode($test->faq);
                }
            }
            if ($item->effectiveness == 40 || $item->effectiveness == 60 || $item->deviation == 40 || $item->deviation == 60) {
                $item->is_valid = false;
            } else {
                $item->is_valid = true;
            }
        }
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
        $this->authorize('test-create');

        if (!$request->balance_test_id) {
            if (!($request->effectiveness == 40 || $request->effectiveness == 60 || $request->deviation == 40 || $request->deviation == 60)) {
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
                    'method' => ['required', 'boolean'],
                    'first_comment' => ['nullable', 'max:255'],
                    'faq' => ['nullable']
                ]);
            }
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
                'first_comment' => $request->first_comment,
                'faq' => is_null($request->faq) ? null : json_encode($request->faq)
            ]);

            if ($balanceTest->first_size) {
                $aggregate = Aggregate::find($request->aggregate_id);
                $ignore = [];
                if ($aggregate->title) {
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

        if ($request->balance_test_id) {
            $balanceTest = BalanceTest::find($request->balance_test_id);
            if (!($request->effectiveness == 40 || $request->effectiveness == 60 || $request->deviation == 40 || $request->deviation == 60)) {
                $request->validate([
                    'size' => ['required', 'integer'],
                    'first_comment' => ['nullable', 'max:255'],
                    'second_comment' => ['nullable', 'max:255'],
                    'nature_control_id' => [
                        'exists:nature_controls,id',
                        new NatureControlRule(natureControlID: $balanceTest->nature_control_id, balanceID: $balanceTest->id)],
                ]);
            }

            if ($request->second_size) {
                $update_data['second_size'] = $request->second_size;
            }if ($request->second_comment) {
                $update_data['second_comment'] = $request->second_comment;
            }if ($request->first_comment) {
                $update_data['first_comment'] = $request->first_comment;
            }
            if ($request->name) {
                $update_data['name'] = $request->name;
            }
            if ($request->first_size) {
                $update_data['first_size'] = $request->first_size;
            }
            if ($request->array_table) {
                $update_data['array_table'] = $request->array_table;
            }
            if ($request->aggregate_id) {
                $update_data['aggregate_id'] = $request->aggregate_id;
            }
            if ($request->deviation) {
                $update_data['deviation'] = $request->deviation;
            }
            if ($request->effectiveness) {
                $update_data['effectiveness'] = $request->effectiveness;
            }
            if ($request->nature_control_id) {
                $update_data['nature_control_id'] = $request->nature_control_id;
            }
            if ($request->balance_item_id) {
                $update_data['balance_item_id'] = $request->balance_item_id;
            }
            if ($request->method) {
                $update_data['method'] = $request->method;
            }
            if ($request->first_comment) {
                $update_data['first_comment'] = $request->first_comment;
            }
            if ($request->faq) {
                $update_data['faq'] = json_encode($request->faq);
            }
            $balanceTest->update($update_data);

            if ($balanceTest->first_size) {
                $aggregate = Aggregate::find($request->aggregate_id);
                $ignore = [];
                if ($aggregate->title) {
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
            if ($balanceTest->second_size) {
                $aggregate = Aggregate::find($balanceTest->aggregate_id);
                $balanceTestExcel = BalanceTestExcel::where('balance_test_id', $balanceTest->id)->first()->data;

                if ($aggregate->title) {
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
        if ($request->effectiveness == 40 || $request->effectiveness == 60 || $request->deviation == 40 || $request->deviation == 60) {
            return response(['message' => 'TOC’s неприменим', 'errors' => ['effectiveness' => ['error'], 'deviation' => ['error']]], 422);
        }
        return response(['message' => 'Success', 'balance_test_id' => $balanceTest->id], 200);
    }

    public function update(BalanceTest $balanceTest, Request $request)
    {
        $request->validate([
            'array_table' => ['required'],
        ]);

        $balanceTest->update(
            [
                'array_table' => $request->array_table
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
        $this->authorize('test-edit', $balanceTest);

        $balanceTest = BalanceTest::where('id', $balanceTest->id)
            ->select('id',
                'name',
                'nature_control_id',
                'first_size as size',
                'array_table',
                'aggregate_id',
                'effectiveness',
                'deviation',
                'balance_item_id',
                'method',
                'first_comment',
                'second_comment',
                'faq'
            )
            ->with(['aggregate', 'natureControl'])->first();
        if (!is_null($balanceTest->faq)) {
            $balanceTest->faq = json_decode($balanceTest->faq);
        }
        if ($balanceTest->effectiveness == 40 || $balanceTest->effectiveness == 60 || $balanceTest->deviation == 40 || $balanceTest->deviation == 60) {
            $balanceTest->is_valid = false;
        } else {
            $balanceTest->is_valid = true;
        }
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
        $this->authorize('test-delete');

        $balanceTest->forceDelete();

        return response(['message' => 'Success'], 200);
    }

    public function excel(BalanceTest $balanceTest)
    {
        $this->authorize('test-create');

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
