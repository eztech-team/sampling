<?php

namespace App\Http\Controllers;

use App\Exports\UsersImport;
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
}
