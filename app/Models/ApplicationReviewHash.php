<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationReviewHash extends Model
{
    use HasFactory;
    protected $fillable = [
        'accountNo', 'hash'
    ];
}
