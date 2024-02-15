<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCoverage extends Model
{
    use HasFactory;
    protected $table = 'stock_coverages';
    protected $primaryKey = 'id';
}
