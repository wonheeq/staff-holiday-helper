<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailPreference extends Model
{
    use HasFactory;
    protected $fillable = [
        'accountNo',
        'hours',
    ];
}
