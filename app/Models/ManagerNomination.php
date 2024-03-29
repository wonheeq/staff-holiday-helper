<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerNomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicationNo',
        'nomineeNo',
        'subordinateNo',
        'status',
    ];
}
