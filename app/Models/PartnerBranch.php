<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerBranch extends Model
{
    use HasFactory;
    protected $table = 'partner_branches';
    protected $primaryKey = 'branch_id';
}