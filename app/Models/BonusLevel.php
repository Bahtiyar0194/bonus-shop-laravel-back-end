<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusLevel extends Model
{
    use HasFactory;
    protected $table = 'bonus_levels';
    protected $primaryKey = 'level_id';
}
