<?php

namespace App\Http\Controllers;

use App\Imports\TdExcelImport;
use App\Models\BalanceItem;
use App\Models\BalanceTest;
use App\Models\IncomeItem;
use App\Models\IncomeTest;
use App\Models\Td;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TdController extends Controller
{
    public function balanceTd()
    {
        request()->validate([
            'project_id' => ['required', 'exists:projects,id']
        ]);

        $items = BalanceItem::where('project_id', request()->project_id)
            ->with('tds:id,name,balance_item_id,status,array_table')
            ->get();

        return response($items, 200);
    }

    public function IncomeTd()
    {
        request()->validate([
            'project_id' => ['required', 'exists:projects,id']
        ]);

        $items = IncomeItem::where('project_id', request()->project_id)
            ->with('tds:id,name,income_item_id,status,array_table')
            ->get();

        return response($items, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'array_table' => ['required', 'array'],
            'stratification' => ['required', 'boolean'],
            'count_stratification' => ['nullable', 'required_if:stratification,=,true'],
            'td_method' => ['integer', 'max:2', 'min:0'],
            'excels' => ['required'],
            'excels.*.aggregate_id' => ['required', 'exists:aggregates,id', 'distinct'],
            'balance_item_id' => ['required_if:income_item_id,=,null', 'exists:balance_items,id'],
            'income_item_id' => ['required_if:balance_item_id,=,null', 'exists:income_items,id']
        ]);

        $td = Td::create($data);

        $td->excels()->attach($request->excels, ['data' => json_encode('123')]);

        return response(['message' => 'Success', 'td_id' => $td->id], 200);
    }

    public function storeMatrix(Request $request)
    {
        $request->validate([
            'material_misstatement' => ['required', 'integer', 'max:2', 'min:0'],
            'control_risk' => ['required', 'integer', 'max:2', 'min:0'],
            'control_risc_comment' => ['nullable'],
            'tocs' => ['nullable'],
            'ratio_expected_error' => ['required'],
            'ratio_expected_error_comment' => ['nullable'],
            'size' => ['required', 'integer'],
        ]);

        $td = Td::where('id', $request->id)->first();

        $td->update([
            'material_misstatement' => $request->material_misstatement,
            'control_risk' => $request->control_risk,
            'control_risc_comment' => $request->control_risc_comment,
            'ratio_expected_error' => $request->ratio_expected_error,
            'size' => $request->size,
            'attempt' => 0,
        ]);

        if($td->balance_item_id){
            $td->update([
                'balance_test_id' => $request->tocs
            ]);
        }
        if($td->income_item_id){
            $td->update([
                'income_test_id' => $request->tocs
            ]);
        }

        return response(['message' => 'Success'], 200);
    }

    public function show(Request $request)
    {
        $td = Td::where('id', $request->id)
            ->with('excels:id,name,path')
            ->select('id', 'array_table', 'stratification', 'count_stratification', 'td_method')
            ->first();

//        $td->setKeyName('td_method_name');
//        $td->setKeyType('string');
//        $td->setAttribute('td_method_name', Td::$methods[$td->td_method]);

        return response($td, 200);
    }

    public function showMatrix(Request $request)
    {
        $tdMatrix = Td::where('id', $request->id)
            ->select('id',
                'material_misstatement',
                'control_risk',
                'control_risc_comment',
                'ratio_expected_error',
                'ratio_expected_error_comment',
                'size',
                'balance_test_id',
                'income_test_id',
            )
            ->first();

//        $tdMatrix->setAttribute(
//            'material_misstatement', Td::$likelihoodOfMaterialMisstatement[$tdMatrix->material_misstatement]
//        );
//        $tdMatrix->setAttribute('control_risk', Td::$controlRisk[$tdMatrix->control_risk]);

        if($tdMatrix->balance_test_id){
            $tdMatrix->setKeyName('balance_test_name');
            $tdMatrix->setKeyType('string');
            $tdMatrix->setAttribute(
                'balance_test_name', BalanceTest::find($tdMatrix->balance_test_id)->name
            );
        }

        if($tdMatrix->income_test_id){
            $tdMatrix->setKeyName('income_test_name');
            $tdMatrix->setKeyType('string');
            $tdMatrix->setAttribute(
                'income_test_name', IncomeTest::find($tdMatrix->income_test_id)->name
            );
        }

        return response($tdMatrix, 200);
    }
}
