<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdExcel extends Model
{
    use HasFactory;

    protected $table = 'aggregate_td_excel';

    protected $guarded = [];
}
