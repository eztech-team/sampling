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
    const BASE_PATH = 'excels/sample/';

    //td_method
    //1 - VALUE-WEIGHTED SELECTION
    //2 - Monetary Unit Sampling (MUS)
    //3 - Haphazard sampling (случайная выборка)
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
                if (file_exists(self::BASE_PATH . "$td->id-vws.xlsx")) {
                    $excel = [
                        'path' => self::BASE_PATH . "$td->id-vws.xlsx",
                        'size' => $td->size + 1,
                    ];
                } else {
                    $excel = $this->valueWeightedSelection($td->id, $excels, $td->size);
                }
                break;
            case 2:
                if (file_exists(self::BASE_PATH . "$td->id-mus.xlsx")) {
                    $excel = [
                        'path' => self::BASE_PATH . "$td->id-mus.xlsx",
                        'size' => $td->size + 1,
                    ];
                } else {
                    $excel = $this->monetaryUnitSampling($td->id, $excels, $td->size);
                }
                break;
            case 3:
                if (file_exists(self::BASE_PATH . "$td->id-haphazard-sampling.xlsx")) {
                    $excel = [
                        'path' => self::BASE_PATH . "$td->id-haphazard-sampling.xlsx",
                        'size' => $td->size + 1,
                    ];
                } else {
                    $excel = $this->haphazardSampling($td->id, $excels, $td->size);
                }
                break;
            default:
                return response(['message' => 'Not found TD'], 400);
        }
        $excel['path'] = env('APP_URL') . '/' . $excel['path'];
        return response($excel, 200);
    }

    /**
     * @throws Exception
     */
    private function haphazardSampling(int $td_id, array $excels, int $sample_size): array
    {
        $sample_rows = [];
        foreach ($excels as $excel_file) {
            $spreadsheet = IOFactory::load($excel_file['path']);
            $worksheet = $spreadsheet->getActiveSheet();
            $random_numbers = [];
            for ($i = 0; $i < $sample_size; $i++) {
                $random_numbers[] = rand(2, $excel_file['amount_column']);
            }
            foreach ($random_numbers as $random_number) {
                $sample_rows[] = $worksheet->rangeToArray("A$random_number:K$random_number", null, true, true, true)[$random_number];
            }
        }
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->fromArray(['Период', '№', 'Счет Дт', 'Количество Дт', 'Валюта Дт', 'Вал. сумма Дт', 'Счет Кт', 'Количество Кт', 'Валюта Кт', 'Вал. сумма Кт', 'Сумма']);

        $row_index = 2;
        foreach ($sample_rows as $row) {
            $worksheet->setCellValue("A$row_index", $row["A"])
                ->setCellValue("B$row_index", $row["B"])
                ->setCellValue("C$row_index", $row["C"])
                ->setCellValue("D$row_index", $row["D"])
                ->setCellValue("E$row_index", $row["E"])
                ->setCellValue("F$row_index", $row["F"])
                ->setCellValue("G$row_index", $row["G"])
                ->setCellValue("H$row_index", $row["H"])
                ->setCellValue("I$row_index", $row["I"])
                ->setCellValue("J$row_index", $row["J"])
                ->setCellValue("K$row_index", $row["K"]);
            $row_index++;
        }

        $path = self::BASE_PATH . "$td_id-haphazard-sampling.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $highestRow = $sample_size + 1;

        return [
            'path' => $path,
            'size' => $highestRow,
        ];
    }

    private function monetaryUnitSampling(int $td_id, array $excels, int $sample_size, int $minimum_value = 1000000): array
    {
        $data = [];

        foreach ($excels as $excel_file) {
            $spreadsheet = IOFactory::load($excel_file['path']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            foreach ($rows as $row) {
                $data[] = [
                    'period' => $row[0],      // A-период
                    'number' => $row[1],      // B-номер
                    'account_dt' => $row[2],  // C-Счет Дт
                    'quantity_dt' => $row[3], // D-Количество Дт
                    'currency_dt' => $row[4], // E-Валюта Дт
                    'amount_dt' => $row[5],   // F-Вал. сумма Дт
                    'account_kt' => $row[6],  // G-Счет Кт
                    'quantity_kt' => $row[7], // H-Количество Кт
                    'currency_kt' => $row[8], // I-Валюта Кт
                    'amount_kt' => $row[9],   // J-Вал. сумма Кт
                    'total' => $row[10],      // K-Сумма
                ];
            }
        }
        $filteredData = [];
        foreach ($data as $item) {
            if (
                $item['amount_dt'] >= $minimum_value &&
                $item['amount_kt'] >= $minimum_value &&
                $item['total'] >= $minimum_value
            ) {
                $filteredData[] = $item;
            }
        }
        if (count($filteredData) < $sample_size) {
            $sample_size = count($filteredData);
        }
        $sample = array_slice($filteredData, 0, $sample_size);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A', 'Период');
        $sheet->setCellValue('B', '№');
        $sheet->setCellValue('C', 'Счет Дт');
        $sheet->setCellValue('D', 'Количество Дт');
        $sheet->setCellValue('E', 'Валюта Дт');
        $sheet->setCellValue('F', 'Вал. сумма Дт');
        $sheet->setCellValue('G', 'Счет Кт');
        $sheet->setCellValue('H', 'Количество Кт');
        $sheet->setCellValue('I', 'Валюта Кт');
        $sheet->setCellValue('J', 'Вал. сумма Кт');
        $sheet->setCellValue('K', 'Сумма');


        $row = 2;
        foreach ($sample as $item) {
            $sheet->setCellValue('A' . $row, $item['period']);
            $sheet->setCellValue('B' . $row, $item['number']);
            $sheet->setCellValue('C' . $row, $item['account_dt']);
            $sheet->setCellValue('D' . $row, $item['quantity_dt']);
            $sheet->setCellValue('E' . $row, $item['currency_dt']);
            $sheet->setCellValue('F' . $row, $item['amount_dt']);
            $sheet->setCellValue('G' . $row, $item['account_kt']);
            $sheet->setCellValue('H' . $row, $item['quantity_kt']);
            $sheet->setCellValue('I' . $row, $item['currency_kt']);
            $sheet->setCellValue('J' . $row, $item['amount_kt']);
            $sheet->setCellValue('K' . $row, $item['total']);

            $row++;
        }
        $path = self::BASE_PATH . "$td_id-mus.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $highestRow = $sample_size + 1;

        return [
            'path' => $path,
            'size' => $highestRow,
        ];
    }

    private function valueWeightedSelection(int $td_id, array $excels, int $sample_size, int $minimum_value = 1000000): array
    {
        $data = [];

        foreach ($excels as $excel_file) {
            $spreadsheet = IOFactory::load($excel_file['path']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            foreach ($rows as $row) {
                $item = [
                    'period' => $row[0],      // A-период
                    'number' => $row[1],      // B-номер
                    'account_dt' => $row[2],  // C-Счет Дт
                    'quantity_dt' => $row[3], // D-Количество Дт
                    'currency_dt' => $row[4], // E-Валюта Дт
                    'amount_dt' => $row[5],   // F-Вал. сумма Дт
                    'account_kt' => $row[6],  // G-Счет Кт
                    'quantity_kt' => $row[7], // H-Количество Кт
                    'currency_kt' => $row[8], // I-Валюта Кт
                    'amount_kt' => $row[9],   // J-Вал. сумма Кт
                    'total' => (int)str_replace(',', '', $row[10]),      // K-Сумма
                ];

                if (
                    $item['amount_dt'] >= $minimum_value &&
                    $item['amount_kt'] >= $minimum_value &&
                    $item['total'] >= $minimum_value
                ) {
                    $data[] = $item;
                }
            }
        }

        unset($data[0]);
        // Шаг 1: Проверка, достаточно ли данных для формирования выборки
        if (count($data) < $sample_size) {
            $sample_size = count($data);
        }

        // Шаг 2: Формирование выборки на основе Value-Weighted Selection
        $sample = [];

        // Вычисление суммарной стоимости (веса) всех элементов данных
        $totalValue = 0;

        foreach ($data as $item) {
            $totalValue += $item['total'];
        }

        // Вычисление веса каждого элемента данных
        $weights = [];
        foreach ($data as $item) {
            $weight = $item['total'] / $totalValue;
            $weights[] = $weight;
        }

        // Выборка элементов на основе весов
        $randomIndexes = [];
        while (count($randomIndexes) < $sample_size) {
            $random = mt_rand() / mt_getrandmax(); // Генерация случайного числа от 0 до 1
            $cumulativeWeight = 0;
            for ($i = 1; $i < count($weights); $i++) {
                $cumulativeWeight += $weights[$i];
                if ($random <= $cumulativeWeight) {
                    $randomIndexes[] = $i;
                    break;
                }
            }
        }
        foreach ($randomIndexes as $index) {
            if ($index > 0) {
                $sample[] = $data[$index];
            }
        }

        // Создание и сохранение Excel-файла
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Период');
        $sheet->setCellValue('B1', '№');
        $sheet->setCellValue('C1', 'Счет Дт');
        $sheet->setCellValue('D1', 'Количество Дт');
        $sheet->setCellValue('E1', 'Валюта Дт');
        $sheet->setCellValue('F1', 'Вал. сумма Дт');
        $sheet->setCellValue('G1', 'Счет Кт');
        $sheet->setCellValue('H1', 'Количество Кт');
        $sheet->setCellValue('I1', 'Валюта Кт');
        $sheet->setCellValue('J1', 'Вал. сумма Кт');
        $sheet->setCellValue('K1', 'Сумма');

        $row = 2;
        foreach ($sample as $item) {
            $sheet->setCellValue('A' . $row, $item['period']);
            $sheet->setCellValue('B' . $row, $item['number']);
            $sheet->setCellValue('C' . $row, $item['account_dt']);
            $sheet->setCellValue('D' . $row, $item['quantity_dt']);
            $sheet->setCellValue('E' . $row, $item['currency_dt']);
            $sheet->setCellValue('F' . $row, $item['amount_dt']);
            $sheet->setCellValue('G' . $row, $item['account_kt']);
            $sheet->setCellValue('H' . $row, $item['quantity_kt']);
            $sheet->setCellValue('I' . $row, $item['currency_kt']);
            $sheet->setCellValue('J' . $row, $item['amount_kt']);
            $sheet->setCellValue('K' . $row, $item['total']);

            $row++;
        }

        $path = self::BASE_PATH . "$td_id-vws.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $highestRow = $sample_size + 1;

        return [
            'path' => $path,
            'size' => $highestRow,
        ];
    }
}
