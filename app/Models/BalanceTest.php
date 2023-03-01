<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BalanceTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

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
}
