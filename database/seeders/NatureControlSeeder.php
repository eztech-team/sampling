<?php

namespace Database\Seeders;

use App\Models\NatureControl;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NatureControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NatureControl::create([
            'name' => 'Manual*** key control performed daily or multiple times per day',
            'size' => 22,
            'first_error' => 2,
            'second_error' => 0,
        ]);
        NatureControl::create([
            'name' => 'Manual key control performed weekly',
            'size' => 5,
            'first_error' => 2,
            'second_error' => 0,
        ]);
        NatureControl::create([
            'name' => 'Manual key control performed monthly',
            'size' => 4,
            'first_error' => 0,
            'second_error' => -1,
        ]);
        NatureControl::create([
            'name' => 'Manual key control performed quarterly',
            'size' => 4,
            'first_error' => 0,
            'second_error' => -1,
        ]);
        NatureControl::create([
            'name' => 'Manual key control performed annually',
            'size' => 0,
            'first_error' => 0,
            'second_error' => -1,
        ]);
        NatureControl::create([
            'name' => 'Automated application control',
            'size' => 12,
            'first_error' => 0,
            'second_error' => -1,
        ]);
        NatureControl::create([
            'name' => 'IT general controls',
            'size' => 0,
            'first_error' => 0,
            'second_error' => 0,
        ]);
        NatureControl::create([
            'name' => 'The number of occurrences ranges between 50-250 times'
        ]);
    }
}
