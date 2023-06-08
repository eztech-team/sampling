<?php

namespace App\Http\Controllers;

use App\Imports\ExcelImport;
use App\Http\Traits\CreateDeleteFiles;
use App\Models\Aggregate;
use App\Models\BalanceTest;use App\Models\IncomeItem;
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
        $this->authorize('test-index');

        $balanceItem = IncomeItem::where('project_id', request()->project_id)
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

    public function store(Request $request)
    {
        $this->authorize('test-create');

        $is_new_or_edit_error = true;
        if ($request->income_test_id) {
            $incomeTest = IncomeTest::find($request->income_test_id);
            if ($incomeTest->effectiveness == 40 || $incomeTest->effectiveness == 60 || $incomeTest->deviation == 40 || $incomeTest->deviation == 60) {
                $is_new_or_edit_error = true;
            } else {
                $is_new_or_edit_error = false;
            }
        }

        if($is_new_or_edit_error){
            if (!($request->effectiveness == 40 || $request->effectiveness == 60 || $request->deviation == 40 || $request->deviation == 60)) {
                $request->validate([
                    'income_test_id' => 'nullable',
                    'name' => ['required', 'max:255'],
                    'size' => ['required', 'integer'],
                    'array_table' => ['required'],
                    'aggregate_id' => ['required', 'exists:aggregates,id'],
                    'deviation' => ['required', 'string'],
                    'effectiveness' => ['required', 'string'],
                    'nature_control_id' => ['required', 'exists:nature_controls,id'],
                    'income_item_id' => ['required', 'exists:income_items,id'],
                    'method' => ['required', 'boolean'],
                    'first_comment' => ['nullable', 'max:255'],
                    'faq' => ['nullable']
                ]);
            }

            if (!isset($incomeTest)) {
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
                    'first_comment' => $request->first_comment,
                    'faq'   => is_null($request->faq) ? null : json_encode($request->faq)
                ]);
            } else {
                $incomeTest->update(
                    [
                        'name' => $request->name,
                        'first_size' => $request->size,
                        'array_table' => $request->array_table,
                        'aggregate_id' => $request->aggregate_id,
                        'deviation' => $request->deviation,
                        'effectiveness' => $request->effectiveness,
                        'nature_control_id' => $request->nature_control_id,
                        'income_item_id' => $request->income_item_id,
                        'method' => $request->method,
                        'first_comment' => $request->first_comment,
                        'faq'   => is_null($request->faq) ? null : json_encode($request->faq)
                    ]
                );
            }
            if($incomeTest->first_size){
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
                        incomeTestID: $incomeTest->id),
                    $aggregate->path
                );
            }
        } else {
            $incomeTest = IncomeTest::find($request->income_test_id);
            $request->validate([
                'size' => ['required', 'integer'],
                'first_comment' => ['nullable', 'max:255'],
                'second_comment' => ['nullable', 'max:255'],
                'nature_control_id' => [
                    'exists:nature_controls,id',
                    new NatureControlRule(natureControlID: $incomeTest->nature_control_id, incomeID: $incomeTest->id)]
            ]);

            $incomeTest->update([
                'second_size' => $request->size,
                'second_comment' => $request->second_comment,
                'first_comment' => $request->first_comment,
            ]);

            if($incomeTest->first_size) {
                $aggregate = Aggregate::find($incomeTest->aggregate_id);
                $incomeTestExcel = IncomeTestExcel::where('income_test_id', $incomeTest->id)->first()->data;

                if ($aggregate->title) {
                    $incomeTestExcel[] = ["row" => 1];
                }

                $ignore = array_column($incomeTestExcel, 'row');

                Excel::import(
                    new ExcelImport(
                        random: $request->size,
                        ignore: $ignore,
                        method: $incomeTest->method,
                        incomeTestID: $incomeTest->id), $aggregate->path);
            }
        }

        if ($request->effectiveness == 40 || $request->effectiveness == 60 || $request->deviation == 40 || $request->deviation == 60) {
            return response(['message' => 'TOC’s неприменим', 'error' => ['effectiveness' => 'effectiveness not applicable', 'deviation' => 'deviation not applicable']], 422);
        }
        return response(['message' => 'Success', 'income_test_id' => $incomeTest->id], 200);
    }

    public function show(IncomeTest $incomeTest)
    {
        $this->authorize('test-edit', $incomeTest);

        $incomeTest = IncomeTest::where('id', $incomeTest->id)
            ->select('id',
                'name',
                'nature_control_id',
                'first_size as size',
                'array_table',
                'aggregate_id',
                'effectiveness',
                'deviation',
                'income_item_id',
                'method',
                'first_comment',
                'second_comment',
                'faq'
            )
            ->with(['aggregate', 'natureControl'])->first();

        if (!is_null($incomeTest->faq)) {
            $incomeTest->faq = json_decode($incomeTest->faq);
        }
        if ($incomeTest->effectiveness == 40 || $incomeTest->effectiveness == 60 || $incomeTest->deviation == 40 || $incomeTest->deviation == 60) {
            $incomeTest->is_valid = false;
        } else {
            $incomeTest->is_valid = true;
        }
        return response($incomeTest,200);
    }

    public function destroy(IncomeTest $incomeTest)
    {
        $this->authorize('test-delete');

        $incomeTest->forceDelete();

        return response(['message' => 'Success'], 200);
    }

    public function excel(IncomeTest $incomeTest)
    {
        $this->authorize('test-create');

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
