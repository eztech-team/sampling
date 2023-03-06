<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'array_table' => 'array'
    ];

    public function incomeItem()
    {
        return $this->belongsTo(IncomeItem::class);
    }

    public function natureControl()
    {
        return $this->belongsTo(NatureControl::class);
    }

    public function aggregate()
    {
        return $this->belongsTo(Aggregate::class);
    }

    public function excel()
    {
        return $this->hasMany(IncomeTestExcel::class);
    }

    protected function data() : Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value)
        );
    }
}
