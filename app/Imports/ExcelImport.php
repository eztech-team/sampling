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
    private $type;

    public function __construct($random, $ignore, $balanceTestId, $type)
    {
        $this->random = $random;
        $this->ignore = $ignore;
        $this->balanceTestId = $balanceTestId;
        $this->type = $type;
    }

    public function collection(Collection $collection)
    {
        $arr = [];

        $items = new Collection();

        foreach ($collection as $value){
            $value['row'] = ++$this->row;

            $items->push($value);
        }

        if($this->type){
            $items = $items->whereNotIn('row', $this->ignore)->random($this->random);
            foreach ($items as $item){
                $item = $item->toArray();
                $arr[] = [
                    'row' => $item['row'],
                ];
            }
        }else{
            $randomItem = $items->whereNotIn('row', $this->ignore)->random();

            $arr[] = [
                'row' => $randomItem->toArray()['row'],
            ];
            $item = 2700 / $this->random;
            for($i = 0; $i < $this->random; $i++){

                $arr[] = [
                    'row' => 1
                ];
            }
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
