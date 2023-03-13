<?php

namespace App\Exports;

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
    protected $balanceTestExcelID;
    protected $incomeTestExcelID;
    private $row = 0;

    public function __construct($data, $name, $path, $title, $balanceTestExcelID = null, $incomeTestExcelID = null)
    {
        $this->data = $data;
        $this->name = $name;
        $this->path = $path;
        $this->title = $title;
        $this->balanceTestExcelID = $balanceTestExcelID;
        $this->incomeTestExcelID = $incomeTestExcelID;
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
            $items = $items->whereIn('row', $dataIDs)->toArray();

            $items[0]['row'] = 'row';
        }else{
            $count = count($items->first());
            $items = $items->whereIn('row', $dataIDs)->toArray();

            for ($i = 0; $i < $count - 1; $i++){
                $items[0][$i] = null;
            }

            $items[0]['row'] = 'row';
            ksort($items);
        }

        $items = collect($items);

        $token = ".";
        $result = explode($token, $this->path, 2)[1];
        $name = str_replace(' ', '', $this->name);

        if($this->incomeTestExcelID){
            $path = 'downloads/' . $name . $this->incomeTestExcelID . '.' .$result;
            Excel::store(new GenerateDateExcel($items), $path);

            DownloadExcel::create([
                'path' => $path,
                'income_test_excel_id' => $this->incomeTestExcelID
            ]);
        }
        if($this->balanceTestExcelID){
            $path = 'downloads/' . $name . $this->balanceTestExcelID . '.' .$result;
            Excel::store(new GenerateDateExcel($items), $path);

            DownloadExcel::create([
                'path' => $path,
                'balance_test_excel_id' => $this->balanceTestExcelID
            ]);
        }
    }
}
