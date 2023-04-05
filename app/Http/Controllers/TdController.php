<?php

namespace App\Http\Controllers;

use App\Imports\TdExcelImport;
use App\Models\Aggregate;
use App\Models\BalanceItem;
use App\Models\BalanceTest;
use App\Models\IncomeItem;
use App\Models\IncomeTest;
use App\Models\Project;
use App\Models\Td;
use App\Models\TdExcel;
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
        $tdID = $td->id;
        $ignore = [];
        foreach ($request->excels as $excelID){
            $excel_id = $excelID['aggregate_id'];

            $path = Aggregate::find($excel_id)->path;
            $amountColumn = Aggregate::find($excel_id)->amount_column;
            $aggregateTitle = Aggregate::find($excel_id)->title;

            if($aggregateTitle) $ignore = [1];

            Excel::import(new TdExcelImport(
                ignore: $ignore,
                excelID: $excelID,
                amountColumn: $amountColumn,
                tdID: $tdID), $path);
        }

        $tdExcelAmount = TdExcel::where('td_id', $tdID)->get()->avg('amount_column');

        if($request->balance_item_id) $projectID = BalanceItem::find($request->balance_item_id)->project_id;
        if($request->income_item_id) $projectID = IncomeItem::find($request->income_item_id)->project_id;

        $operating_level = Project::find($projectID)->operating_level;

        return response([
            'message' => 'Success',
            'td_id' => $tdID,
            'amount_sum' => $tdExcelAmount,
            'operating_level' => $operating_level,
        ], 200);
    }

    public function storeMatrix(Request $request)
    {
        $data = $request->validate([
            'material_misstatement' => ['required', 'integer', 'max:2', 'min:0'],
            'magnitude' => ['required', 'integer', 'max:2', 'min:0'],
            'inherent_risk' => ['required', 'integer', 'max:2', 'min:0'],
            'control_risk' => ['required', 'integer', 'max:2', 'min:0'],
            'control_risc_comment' => ['nullable'],
            'auditor_confidence_level' => ['required', 'string'],
            'misstatement_percentage' => ['required', 'string'],
            'tocs' => ['nullable'],
            'ratio_expected_error' => ['required'],
            'ratio_expected_error_comment' => ['nullable'],
            'size' => ['required', 'integer'],
        ]);

        $td = Td::where('id', $request->id)->first();

        $data['attempt'] = 0;

        $td->update($data);

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

    public function show($id)
    {
        try {
            $td = Td::where('id', $id)
                ->with('excels:id as aggregate_id,name,path')
                ->select('id',
                    'array_table',
                    'stratification',
                    'count_stratification',
                    'td_method',
                    'name',
                    'balance_item_id',
                    'income_item_id'
                )
                ->first();
            $tdExcelAmount = TdExcel::where('td_id', $td->id)->get()->avg('amount_column');

            if($td->balance_item_id) $projectID = BalanceItem::find($td->balance_item_id)->project_id;
            if($td->income_item_id) $projectID = IncomeItem::find($td->income_item_id)->project_id;

            $operating_level = Project::find($projectID)->operating_level;
            return response(
                [
                    'td' => $td,
                    'excel_amount' => $tdExcelAmount,
                    'operating_level' => $operating_level
                ], 200
            );
        }catch (\Exception $e){
            return response(['message' => 'Not found TD'], 400);
        }

    }

    public function showMatrix(Request $request)
    {
        try {
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
                    'magnitude',
                    'inherent_risk',
                    'auditor_confidence_level',
                    'misstatement_percentage',
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
        }catch (\Exception $e){
            return response(['message' => 'Not found TDs matrix'], 400);
        }

    }
}
