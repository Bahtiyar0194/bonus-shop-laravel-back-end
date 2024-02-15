<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchRegulation extends Model
{
    use HasFactory;
    protected $table = 'branch_regulations';
    protected $primaryKey = 'id';
}
