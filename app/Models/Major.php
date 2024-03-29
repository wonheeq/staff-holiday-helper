<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;
    protected $primaryKey = 'majorId';
    public $incrementing = false;
    protected $fillable = ['majorId', 'name'];
}
