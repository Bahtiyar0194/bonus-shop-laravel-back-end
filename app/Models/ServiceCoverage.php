<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCoverage extends Model
{
    use HasFactory;
    protected $table = 'services_coverages';
    protected $primaryKey = 'id';
}
