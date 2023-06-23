<?php

namespace App\Http\Controllers;

use App\Models\Aggregate;
use App\Models\Td;
use App\Models\TdExcel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;

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
//                if (file_exists(self::BASE_PATH . "$td->id-vws.xlsx")) {
//                    $excel = [
//                        'path' => self::BASE_PATH . "$td->id-vws.xlsx",
//                        'size' => $td->size + 1,
//                    ];
//                } else {
                    $excel = $this->valueWeightedSelection($td->id, $excels, $td->size);
//                }
                break;
            case 2:
//                if (file_exists(self::BASE_PATH . "$td->id-mus.xlsx")) {
//                    $excel = [
//                        'path' => self::BASE_PATH . "$td->id-mus.xlsx",
//                        'size' => $td->size + 1,
//                    ];
//                } else {
                    $excel = $this->monetaryUnitSampling($td->id, $excels, $td->size);
//                }
                break;
            case 3:
//                if (file_exists(self::BASE_PATH . "$td->id-haphazard-sampling.xlsx")) {
//                    $excel = [
//                        'path' => self::BASE_PATH . "$td->id-haphazard-sampling.xlsx",
//                        'size' => $td->size + 1,
//                    ];
//                } else {
                    $excel = $this->haphazardSampling($td->id, $excels, $td->size);
//                }
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
        $total_population = 0; //total_population - общая сумма всех excel файлов
        $count_population = 0; //count_population - количество строк в excel файлах
        $count_sample     = 0; //count_sample - длинна итогового excel файла
        $total_sample     = 0; //total_sample - общая цена итого excel файла

        foreach ($excels as $excel_file) {
            $spreadsheet = IOFactory::load($excel_file['path']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            unset($rows[0]);
            foreach ($rows as $row) {
                $total_population += (int)str_replace(',', '', $row[$excel_file['amount_column']  + 1]);
                $count_population += 1;
            }
            $random_numbers = [];
            for ($i = 0; $i < ($sample_size/count($excels)); $i++) {
                $random_numbers[] = rand(1, count($rows));
            }
            foreach ($random_numbers as $random_number) {
                $item = $rows[$random_number];
                unset($item[0]);
                $item['total'] = (int)str_replace(',', '',$item[$excel_file['amount_column']  + 1]);
                $sample_rows[] = $item;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        foreach ($sample_rows as $item) {
            $sheet->setCellValue('A' . $row, $item['total']);
            $total_sample     += $item['total'];
            unset($item['total']);
            foreach ($item as $key => $value) {
                $sheet->setCellValue($this->getCoordinate($key) . $row, $value);
            }
            $row++;
            $count_sample     += 1;
        }
        $path = self::BASE_PATH . "$td_id-haphazard-sampling.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $highestRow = $sample_size + 1;
        Td::query()
            ->where('id', $td_id)
            ->update(
                [
                    'sample_data' => [
                        'total_population' => $total_population,
                        'count_population' => $count_population,
                        'count_sample'     => $count_sample,
                        'total_sample'     => $total_sample,
                    ]
                ]
            );

        return [
            'path' => $path,
            'size' => $highestRow,
        ];
    }

    private function monetaryUnitSampling(int $td_id, array $excels, int $sample_size, int $minimum_value = 1000000): array
    {
        $data = [];
        $total_population = 0; //total_population - общая сумма всех excel файлов
        $count_population = 0; //count_population - количество строк в excel файлах
        $count_sample     = 0; //count_sample - длинна итогового excel файла
        $total_sample     = 0; //total_sample - общая цена итого excel файла

        foreach ($excels as $excel_file) {
            $spreadsheet = IOFactory::load($excel_file['path']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            unset($rows[0]);

            foreach ($rows as $row) {
                $total_population += (int)str_replace(',', '', $row[$excel_file['amount_column']  + 1]);
                $count_population += 1;
                unset($row[0]);
                $row['total'] = (int)str_replace(',', '',$row[$excel_file['amount_column']  + 1]);
                $data[] = $row;
            }
        }

        $filteredData = [];
        foreach ($data as $item) {
            if (
                $item['total'] >= $minimum_value
            ) {
                $filteredData[] = $item;
            }
        }
        if (count($filteredData) < $sample_size) {
            $sample_size = count($filteredData);
        }
        $sample = array_slice($filteredData, 0, $sample_size);

        // Создание и сохранение Excel-файла
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;
        foreach ($sample as $item) {
            $sheet->setCellValue('A' . $row, $item['total']);
            $total_sample     += $item['total'];
            unset($item['total']);
            foreach ($item as $key => $value) {
                $sheet->setCellValue($this->getCoordinate($key) . $row, $value);
            }
            $row++;
            $count_sample     += 1;
        }
        $path = self::BASE_PATH . "$td_id-mus.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $highestRow = $sample_size + 1;
        Td::query()
            ->where('id', $td_id)
            ->update(
                [
                    'sample_data' => [
                        'total_population' => $total_population,
                        'count_population' => $count_population,
                        'count_sample'     => $count_sample,
                        'total_sample'     => $total_sample,
                    ]
                ]
            );
        return [
            'path' => $path,
            'size' => $highestRow,
        ];
    }

    private function valueWeightedSelection(int $td_id, array $excels, int $sample_size): array
    {
        $data = [];
        $total_population = 0; //total_population - общая сумма всех excel файлов
        $count_population = 0; //count_population - количество строк в excel файлах
        $count_sample     = 0; //count_sample - длинна итогового excel файла
        $total_sample     = 0; //total_sample - общая цена итого excel файла
        foreach ($excels as $excel_file) {
            $spreadsheet = IOFactory::load(Storage::path($excel_file['path']));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            unset($rows[0]);
            foreach ($rows as $row) {
                $total_population += (int)str_replace(',', '', $row[$excel_file['amount_column']  + 1]);
                $count_population += 1;
                unset($row[0]);
                $row['total'] = (int)str_replace(',', '',$row[$excel_file['amount_column']  + 1]);
                $data[] = $row;
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

        $row = 1;
        foreach ($sample as $item) {
            $sheet->setCellValue('A' . $row, $item['total']);
            $total_sample     += $item['total'];
            unset($item['total']);
            foreach ($item as $key => $value) {
                $sheet->setCellValue($this->getCoordinate($key) . $row, $value);
            }
            $row++;
            $count_sample     += 1;
        }

        $path = self::BASE_PATH . "$td_id-vws.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $highestRow = $sample_size + 1;

        Td::query()
            ->where('id', $td_id)
            ->update(
                [
                    'sample_data' => [
                        'total_population' => $total_population,
                        'count_population' => $count_population,
                        'count_sample'     => $count_sample,
                        'total_sample'     => $total_sample,
                    ]
                ]
            );

        return [
            'path' => $path,
            'size' => $highestRow,
        ];
    }
    public function calculateMisstatement(Request $request, int $td_id)
    {
        $data = $request->validate([
            'misstatement_method' => ['required', 'integer', 'max:3', 'min:1'],
            'comment'     => ['string', 'nullable'],
            'link'        => ['string', 'nullable'],
            'total_error' => ['integer', 'required']
        ]);
        $td = Td::query()
            ->where('id', $td_id)
            ->first();
        $sample_data = json_decode($td->sample_data, true);

        switch ($data['misstatement_method']) {
            case 1:
                $misstatement = $this->misstatementRatio($data['total_error'], $sample_data['total_sample'], $sample_data['total_population']);
                break;
            case 2:
                $misstatement = $this->averageMisstatement($data['total_error'], $sample_data['count_sample'], $sample_data['count_population']);
                break;
            case 3:
                $misstatement = $this->intervalMisstatementRate($sample_data['total_population'], $td->size);
                break;
        }
        $result = [
            'misstatement' => [
                'misstatement'        => $misstatement,
                'comment'             => $data['comment'] ?? null,
                'link'                => $data['link'] ?? null,
                'total_error'         => $data['total_error'],
                'misstatement_method' => $data['misstatement_method']
            ]
        ];

        Td::query()
            ->where('id', $td_id)
            ->update(
                $result
            );
        return response($result);
    }

    private function misstatementRatio(int $total_error, int $total_sample, int $total_population): int
    {
        $misstatement_rate = $total_error/$total_sample;
        return $total_population * $misstatement_rate;
    }

    private function averageMisstatement(int $total_error, int $count_sample, int $count_population): int
    {
        $average_misstatement = $total_error/$count_sample;
        return $average_misstatement*$count_population;
    }
    private function intervalMisstatementRate(int $total_population, int $td_size): int
    {
        $interval = $total_population/$td_size;
        return ($td_size/$td_size * $interval) + ((2 * $interval)/($interval * 2));
    }

    private function getCoordinate($columnIndex): string
    {
        $letters = range('A', 'Z');
        $columnAddress = '';

        while ($columnIndex >= 0) {
            $remainder = $columnIndex % 26;
            $columnAddress = $letters[$remainder] . $columnAddress;
            $columnIndex = intval($columnIndex / 26) - 1;
        }

        return $columnAddress;
    }
}
