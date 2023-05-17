<?php

namespace App\Http\Controllers;

use App\Models\Aggregate;
use App\Models\Td;
use App\Models\TdExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SampleController extends Controller
{
    //td_method
    //0 - Haphazard sampling (случайная выборка)
    //1 - Monetary Unit Sampling (MUS)
    //2 - hard
    /**
     * @throws Exception
     */
    public function download(int $id)
    {
        $td = Td::query()
            ->where('id', $id)
            ->first();

        $excels = []; //todo оптимизировать
        $td_excels = TdExcel::query()
            ->where('td_id', $id)
            ->get();
        foreach ($td_excels as $excel) {
            $aggregate = Aggregate::query()->where('id', $excel->aggregate_id)->first();
            $excels[] = [
                'path' => $aggregate->path,
                'amount_column' => $aggregate->amount_column
            ];
        }

        switch ($td->td_method) {
            case 1:
                $excel = $this->haphazardSampling($td->id, $excels, $td->size);
                break;
            case 2:
                $excel = $this->monetaryUnitSampling($td->id, $excels, $td->size);
                break;
            case 3:
                $excel = $this->valueWeightedSelection($td->id, $excels, $td->size);
                break;
            default:
                return response(['message' => 'Not found TD'], 400);
        }
        return response()->download($excel)->deleteFileAfterSend(true);
    }

    /**
     * @throws Exception
     */
    private function haphazardSampling(int $td_id, array $excels, int $sample_size): string
    {
        $sample_rows = [];
        // Цикл по файлам Excel
        foreach ($excels as $excel_file) {
            // Создание объекта PhpSpreadsheet для чтения данных из файла
            $spreadsheet = IOFactory::load($excel_file['path']);

            // Получение объекта первого листа
            $worksheet = $spreadsheet->getActiveSheet();

            // Генерация случайных чисел для выборки
            $random_numbers = [];
            for ($i = 0; $i < $sample_size; $i++) {
                $random_numbers[] = rand(2, $excel_file['amount_column']); // Начинаем с 2-й строки, так как первая строка содержит заголовки столбцов
            }

            // Получение случайных строк из листа
            foreach ($random_numbers as $random_number) {
                $sample_rows[] = $worksheet->rangeToArray("A{$random_number}:K$random_number", null, true, true, true)[$random_number];
            }
        }
        // Создание нового Excel-файла
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        // Запись заголовков столбцов в первую строку
        $worksheet->fromArray(['Период', '№', 'Счет Дт', 'Количество Дт', 'Валюта Дт', 'Вал. сумма Дт', 'Счет Кт', 'Количество Кт', 'Валюта Кт', 'Вал. сумма Кт', 'Сумма']);

        // Запись выбранных строк в файл
        $row_index = 2;
        foreach ($sample_rows as $row) {
            $worksheet->setCellValue("A{$row_index}", $row["A"])
                ->setCellValue("B{$row_index}", $row["B"])
                ->setCellValue("C{$row_index}", $row["C"])
                ->setCellValue("D{$row_index}", $row["D"])
                ->setCellValue("E{$row_index}", $row["E"])
                ->setCellValue("F{$row_index}", $row["F"])
                ->setCellValue("G{$row_index}", $row["G"])
                ->setCellValue("H{$row_index}", $row["H"])
                ->setCellValue("I{$row_index}", $row["I"])
                ->setCellValue("J{$row_index}", $row["J"])
                ->setCellValue("K{$row_index}", $row["K"]);
            $row_index++;
        }

        $uniq = uniqid();
        $path = "excels/$td_id-$uniq.xlsx";

        // Сохранение файла
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        return $path;
    }

    private function monetaryUnitSampling(int $td_id, array $excels, int $sample_size, int $minimum_value = 1000000): string
    {
        $sample_rows = [];
        foreach ($excels as $excel_file) {
            $spreadsheet = IOFactory::load($excel_file['path']);

            // Получение объекта первого листа
            $worksheet = $spreadsheet->getActiveSheet();

            // Чтение данных из столбца с денежной стоимостью
            $column_index = 'K'; // Здесь K - столбец суммы
            $highest_row = $worksheet->getHighestRow();
            $column_values = $worksheet->rangeToArray("{$column_index}2:{$column_index}{$highest_row}", null, true, true, true);

            // Выборка строк на основе денежной стоимости
            foreach ($column_values as $row_index => $row) {
                $value = $row["K"]; // Здесь 1 - индекс столбца суммы
                if ($value >= $minimum_value) {
                    $sample_rows[] = $worksheet->rangeToArray("A{$row_index}:K{$row_index}", null, true, true, true)[$row_index];
                }
            }
        }

// Создание нового Excel-файла
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

// Запись заголовков столбцов в первую строку
        $worksheet->fromArray(['Period', 'Number', 'Account Dt', 'Amount Dt', 'Currency Dt', 'Currency Amount Dt', 'Account Kt', 'Amount Kt', 'Currency Kt', 'Currency Amount Kt', 'Sum']);

// Запись выбранных строк в файл
        $row_index = 2;
        foreach ($sample_rows as $row) {
            $worksheet->setCellValue("A{$row_index}", $row["A"])
                ->setCellValue("B{$row_index}", $row["B"])
                ->setCellValue("C{$row_index}", $row["C"])
                ->setCellValue("D{$row_index}", $row["D"])
                ->setCellValue("E{$row_index}", $row["E"])
                ->setCellValue("F{$row_index}", $row["F"])
                ->setCellValue("G{$row_index}", $row["G"])
                ->setCellValue("H{$row_index}", $row["H"])
                ->setCellValue("I{$row_index}", $row["I"])
                ->setCellValue("J{$row_index}", $row["J"])
                ->setCellValue("K{$row_index}", $row["K"]);
            $row_index++;
        }
        $uniq = uniqid();
        $path = "excels/$td_id-$uniq.xlsx";

        // Сохранение файла
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        return $path;
    }

    private function valueWeightedSelection(int $td_id, array $excels, int $sample_size, int $minimum_value = 1000000): string
    {
// Столбец с весовыми значениями
        $weight_column = 'L'; // Здесь L - столбец с весовыми значениями

// Список выбранных строк
        $sample_rows = [];

// Цикл по файлам Excel
        foreach ($excels as $excel_file) {
            // Создание объекта PhpSpreadsheet для чтения данных из файла
            $spreadsheet = IOFactory::load($excel_file['path']);

            // Получение объекта первого листа
            $worksheet = $spreadsheet->getActiveSheet();

            // Чтение данных из столбца с весовыми значениями
            $highest_row = $worksheet->getHighestRow();
            $weight_values = $worksheet->rangeToArray("{$weight_column}2:{$weight_column}{$highest_row}", null, true, true, true);

            // Вычисление общей суммы весовых значений
            $total_weight = 0;

            foreach ($weight_values as $row) {
                $value = $row["L"]; // Здесь 1 - индекс столбца с весовыми значениями
                $total_weight += $value;
            }

            // Выборка строк на основе взвешенного значения
            for ($i = 0; $i < $sample_size; $i++) {
                $random_number = mt_rand(0, $total_weight);
                $selected_row = null;

                foreach ($weight_values as $row_index => $row) {
                    $value = $row["L"]; // Здесь 1 - индекс столбца с весовыми значениями
                    if ($random_number <= $value) {
                        $selected_row = $worksheet->rangeToArray("A{$row_index}:K{$row_index}", null, true, true, true)[$row_index];
                        break;
                    } else {
                        $random_number -= $value;
                    }
                }

                if ($selected_row !== null) {
                    $sample_rows[] = $selected_row;
                }
            }
        }

// Создание нового Excel-файла
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

// Запись заголовков столбцов в первую строку
        $worksheet->fromArray(['Period', 'Number', 'Account Dt', 'Amount Dt', 'Currency Dt', 'Currency Amount Dt', 'Account Kt', 'Amount Kt', 'Currency Kt', 'Currency Amount Kt', 'Sum']);

// Запись выбранных строк в файл
        $row_index = 2;
        foreach ($sample_rows as $row) {
            $worksheet->setCellValue("A{$row_index}", $row["A"])
                ->setCellValue("B{$row_index}", $row["B"])
                ->setCellValue("C{$row_index}", $row["C"])
                ->setCellValue("D{$row_index}", $row["D"])
                ->setCellValue("E{$row_index}", $row["E"])
                ->setCellValue("F{$row_index}", $row["F"])
                ->setCellValue("G{$row_index}", $row["G"])
                ->setCellValue("H{$row_index}", $row["H"])
                ->setCellValue("I{$row_index}", $row["I"])
                ->setCellValue("J{$row_index}", $row["J"])
                ->setCellValue("K{$row_index}", $row["K"]);
            $row_index++;
        }
        $uniq = uniqid();
        $path = "excels/$td_id-$uniq.xlsx";

        // Сохранение файла
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        return $path;
    }
}
