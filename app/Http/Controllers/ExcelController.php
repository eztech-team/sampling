<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Models\Aggregate;
use App\Models\BalanceTest;
use App\Models\BalanceTestExcel;
use App\Models\DownloadExcel;
use App\Models\IncomeTestExcel;
use Illuminate\Http\Request;
use App\Http\Traits\CreateDeleteFiles;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    use CreateDeleteFiles;

    public function downloadBalance(Request $request)
    {
        $this->authorize('test-create');

        $request->validate([
            'balance_test_excel_id' => ['required', 'exists:balance_test_excels,id']
        ]);
        $balanceTestExcel = BalanceTestExcel::where('id', $request->balance_test_excel_id)->first();

        $path = DownloadExcel::where('balance_test_excel_id', $balanceTestExcel->id)->first();

        if(!$path) {
            $balanceTest = BalanceTest::where('id', $balanceTestExcel->balance_test_id)->first();

            $excel = Aggregate::where('id', $balanceTest->aggregate_id)->first();

            Excel::import(
                new ExcelExport(
                    data: $balanceTestExcel->data,
                    name: $excel->name,
                    path: $excel->path,
                    title: $excel->title,
                    balanceTestExcelID: $balanceTestExcel->id), $excel->path);

            $path = DownloadExcel::where('balance_test_excel_id', $balanceTestExcel->id)->first();

            response(['path' => $path->path], 200);
        }

        return response(['path' => $path->path], 200);
    }

    public function downloadIncome(Request $request)
    {
        $this->authorize('test-create');

        $request->validate([
            'income_test_excel_id' => ['required', 'exists:income_test_excels,id']
        ]);

        $incomeTestExcel = IncomeTestExcel::where('id', $request->income_test_excel_id)->first();
        $path = DownloadExcel::where('income_test_excel_id', $incomeTestExcel->id)->first();

        if(!$path) {
            $incomeTest = BalanceTest::where('id', $incomeTestExcel->income_test_id)->first();
            $excel = Aggregate::where('id', $incomeTest->aggregate_id)->first();
            Excel::import(
                new ExcelExport(
                    data: $incomeTestExcel->data,
                    name: $excel->name, path: $excel->path,
                    title: $excel->title,
                    incomeTestExcelID: $incomeTestExcel->id), $excel->path);

            $path = DownloadExcel::where('income_test_excel_id', $incomeTestExcel->id)->first();

            response(['path' => $path->path], 200);
        }

        return response(['path' => $path->path], 200);
    }
}
