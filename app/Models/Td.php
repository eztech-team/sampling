<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Td extends Model
{
    use HasFactory;

    public static array $methods = [
        'VALUE-WEIGHTED SELECTION 1',
        'VALUE-WEIGHTED SELECTION 2 ',
        'VALUE-WEIGHTED SELECTION 3',
    ];

    public static array $likelihoodOfMaterialMisstatement = [
        'High/ Significant 1',
        'High/ Significant 2',
        'High/ Significant 3',
    ];

    public static array $controlRisk = [
        'High 1',
        'High 2',
        'High 3',
    ];

    protected $fillable = [
        'name',
        'array_table',
        'stratification',
        'count_stratification',
        'td_method',
        'balance_item_id',
        'income_item_id',
        'material_misstatement',
        'control_risk',
        'size',
        'ratio_expected_error_comment',
        'ratio_expected_error',
        'control_risc_comment',
        'status',
        'balance_test_id',
        'income_test_id',
    ];

    protected $casts = [
        'array_table' => 'array',
    ];

    public function excels()
    {
        return $this->belongsToMany(Aggregate::class, 'aggregate_td_excel');
    }

    protected function data() : Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value)
        );
    }
}
