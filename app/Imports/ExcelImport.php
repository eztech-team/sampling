<?php

namespace App\Imports;

use App\Models\BalanceTestExcel;
use App\Models\IncomeTestExcel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ExcelImport implements ToCollection
{
    private $row = 0;
    private $balanceTestId;
    private $ignore;
    private $random;
    private $method;
    protected $incomeTestID;
    private $arr;

    public function __construct($random, $ignore, $method, $balanceTestId = null, $incomeTestID = null)
    {
        $this->random = $random;
        $this->ignore = $ignore;
        $this->balanceTestId = $balanceTestId;
        $this->method = $method;
        $this->incomeTestID = $incomeTestID;
        $this->arr = [];
    }

    public function collection(Collection $collection)
    {
        $items = new Collection();

        foreach ($collection as $value){
            $value['row'] = ++$this->row;
            $items->push($value);
        }
        //this row for static method
        $row = $value['row'] - count($this->ignore);

        if($this->method){
            $this->getDateByRandom($items);
        }else{
            $this->getDateBySystematic($items, $row);
        }

        if($this->balanceTestId){
            $balanceTestExcel = BalanceTestExcel::where('balance_test_id', '=' ,$this->balanceTestId)->get();

            if(!$balanceTestExcel or $balanceTestExcel->count() <= 1){
                BalanceTestExcel::create([
                    'data' => $this->arr,
                    'balance_test_id' => $this->balanceTestId
                ]);
            }
        }

        if($this->incomeTestID){
            $incomeTestExcel = IncomeTestExcel::where('income_test_id', '=' ,$this->incomeTestID)->get();

            if(!$incomeTestExcel or $incomeTestExcel->count() <= 1){
                IncomeTestExcel::create([
                    'data' => $this->arr,
                    'income_test_id' => $this->incomeTestID
                ]);
            }
        }

    }

    private function getDateByRandom($items){
        $items = $items->whereNotIn('row', $this->ignore)->random($this->random);
        foreach ($items as $item){
            $item = $item->toArray();
            $this->arr[] = [
                'row' => $item['row'],
            ];
        }
    }

    private function getDateBySystematic($items, $row){
        $counter = $items->whereNotIn('row', $this->ignore)->random()->toArray()['row'];

        $sum = (int)($row / $this->random);

        for($i = 0; $i < $this->random; $i++){
            if($counter + $sum > $row){
                $counter = $counter + $sum - $row;
            }else{
                $counter = $counter + $sum;
            }
            $this->arr[] = [
                'row' => $counter,
            ];
        }
    }
}
