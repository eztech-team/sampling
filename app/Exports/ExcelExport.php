<?php

namespace App\Exports;

use App\Imports\GetExcelData;
use App\Models\DownloadExcel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExport implements ToCollection
{
    protected $data;
    protected $name;
    protected $path;
    protected $title;
    protected $balanceTestID;
    private $row = 0;

    public function __construct($data, $name, $path, $title, $balanceTestID)
    {
        $this->data = $data;
        $this->name = $name;
        $this->path = $path;
        $this->title = $title;
        $this->balanceTestID = $balanceTestID;
    }

    public function collection(Collection $collection)
    {
        $items = new Collection();

        foreach ($collection as $value){
            $value['row'] = ++$this->row;

            $items->push($value);
        }

        $dataIDs = array_column($this->data, 'row');

        if($this->title){
            $dataIDs[] = 1;
        }

        $token = ".";
        $result = explode($token, $this->path, 2)[1];
        $name = str_replace(' ', '', $this->name);

        $path = 'downloads/' . $name . $this->balanceTestID . '.' .$result;

        Excel::store(new GenerateDateExcel($items->whereIn('row', $dataIDs)), $path);

        DownloadExcel::create([
            'path' => $path,
            'balance_test_id' => $this->balanceTestID
        ]);
    }
}
