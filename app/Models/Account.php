<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $primaryKey = 'accountNo';

    // protected $table = 'accounts';
    // protected $guarded = [];
}
