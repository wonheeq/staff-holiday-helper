<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeHash extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountNo', 'hash'
    ];
}
