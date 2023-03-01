<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $randomRows = $rows->random(2);
        foreach ($rows as $row){
            dd($rows->random(2));
        }
    }
}
