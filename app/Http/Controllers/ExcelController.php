<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Exports\UsersImport;
use App\Models\Aggregate;
use App\Models\BalanceTest;
use App\Models\BalanceTestExcel;
use App\Models\DownloadExcel;
use Illuminate\Http\Request;
use App\Http\Traits\CreateDeleteFiles;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    use CreateDeleteFiles;

    public function store(Request $request)
    {
//        $pathToCsv = $this->storeFile('excel', 'excels', $request);
        Excel::import(new UsersImport(), 'excels/bZsiNCQyu1iL9yu5a4KyfYZcf4SpYcmdG2Y3tThz.xls');

    }

    public function download(Request $request)
    {
        $request->validate([
            'balance_test_excel_id' => ['required', 'exists:balance_test_excels,id']
        ]);
        $balanceTestExcel = BalanceTestExcel::where('id', $request->balance_test_excel_id)->first();

        $path = DownloadExcel::where('balance_test_id', $balanceTestExcel->id)->first();

        if(!$path) {
            $balanceTest = BalanceTest::where('id', $balanceTestExcel->balance_test_id)->first();

            $excel = Aggregate::where('id', $balanceTest->aggregate_id)->first();

            Excel::import(new ExcelExport($balanceTestExcel->data, $excel->name, $excel->path, $excel->title, $balanceTestExcel->id), $excel->path);

            $path = DownloadExcel::where('balance_test_id', $balanceTestExcel->id)->first();

            response(['path' => $path->path], 200);
        }

        return response(['path' => $path->path], 200);
    }
}
