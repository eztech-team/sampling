<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BalanceTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'array_table' => 'array'
    ];

    public function balanceItem()
    {
        return $this->hasOne(BalanceItem::class);
    }

    public function natureControl()
    {
        return $this->hasOne(NatureControl::class);
    }

    public function aggregate()
    {
        return $this->hasOne(Aggregate::class);
    }

    protected function data() : Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value)
        );
    }
}
