<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [
            "Астана",
            "Актау",
            "Актобе",
            "Алматы",
            "Атырау",
            "Байконур",
            "Балхаш",
            "Жанаозен",
            "Жезказган",
            "Караганда",
            "Кентау",
            "Кокшетау",
            "Костанай",
            "Кульсары",
            "Кызылорда",
            "Кызылорда",
            "Павлодар",
            "Петропавловск",
            "Риддер",
            "Рудный",
            "Сарканд",
            "Семей",
            "Талдыкорган",
            "Тараз",
            "Темиртау",
            "Туркестан",
            "Уральск",
            "Усть-Каменогорск",
            "Шымкент",
            "Экибастуз",
            "Другое",
        ];
        foreach($cities as $city){
            City::create([
                'name' => $city,
            ]);
        }
    }
}
