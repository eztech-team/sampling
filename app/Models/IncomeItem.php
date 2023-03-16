<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'array_table',
        'project_id'
    ];

    protected $casts = [
        'array_table' => 'array',
    ];

    public function tests()
    {
        return $this->hasMany(IncomeTest::class)
            ->where('deleted_at', null)
            ->orderBy('id', 'asc');
    }

    protected function data() : Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value)
        );
    }
}
