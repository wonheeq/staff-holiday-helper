<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountRole extends Model
{
    use HasFactory;
    protected $primaryKey = 'accountRoleId';
    protected $fillable = ['accountNo', 'roleId', 'unitId', 'schoolId'];
}
