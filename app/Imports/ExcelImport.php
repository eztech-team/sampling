<?php

namespace App\Imports;

use App\Models\BalanceTestExcel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ExcelImport implements ToCollection
{
    private $row = 0;
    private $balanceTestId;
    private $ignore;
    private $random;
    private $sum;

    public function __construct($random, $ignore, $balanceTestId, $sum)
    {
        $this->random = $random;
        $this->ignore = $ignore;
        $this->balanceTestId = $balanceTestId;
        $this->sum = $sum;
    }

    public function collection(Collection $collection)
    {
        $arr = [];

        $items = new Collection();

        foreach ($collection as $value){
            $value['row'] = ++$this->row;

            $items->push($value);
        }

        $items = $items->whereNotIn('row', $this->ignore)->random($this->random);
        foreach ($items as $item){
            $item = $item->toArray();
            $arr[] = [
                'row' => $item['row'],
                'sum' => $item[10]
            ];
        }

        $balanceTestExcel = BalanceTestExcel::where('balance_test_id', '=' ,$this->balanceTestId)->get();

        if(!$balanceTestExcel or $balanceTestExcel->count() <= 1){
            BalanceTestExcel::create([
                'data' => $arr,
                'balance_test_id' => $this->balanceTestId
            ]);
        }
    }
}
