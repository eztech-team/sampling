<?php

namespace App\Imports;

use App\Models\BalanceTestExcel;
use App\Models\IncomeTestExcel;
use App\Models\Td;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TdExcelImport implements ToCollection
{
    private $row = 0;
    private $excelID;
    private $tdID;
    private $ignore;
    private $amountColumn;
    private $arr;

    public function __construct($ignore, $excelID, $amountColumn, $tdID)
    {
        $this->excelID = $excelID;
        $this->tdID = $tdID;
        $this->amountColumn = $amountColumn;
        $this->ignore = $ignore;
        $this->arr = [];
    }

    public function collection(Collection $collection)
    {
        $items = new Collection();

        foreach ($collection as $value){
            $value['row'] = ++$this->row;
            $items->push($value);
        }
        $items = $items->whereNotIn('row', $this->ignore);
        $totalAmount = 0;

        foreach ($items as $item){
            $totalAmount = $totalAmount + $item[$this->amountColumn];
        }

        $td = Td::find($this->tdID);
        $td->excels()->attach($this->excelID, ['amount_column' => (int)$totalAmount]);
    }

}
