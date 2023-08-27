<?php

namespace App\Models;

use App\Mail\TestMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Mail;
// use Illuminate\Auth\Notifications\ResetPassword;
use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notification;
use App\Mail\MJML;


class Account extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    // set accountNo as primary key so eloquent doesn't have a fit about columns
    protected $primaryKey = 'accountNo';
    public $incrementing = false;

    protected $fillable = [
        'accountNo',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }
    public function getAuthIdentifier()
    {
        return $this->accountNo;
    }
    public function getAuthIdentifierName()
    {
        return 'accountNo';
    }
    public function getEmailForPasswordReset()
    {
        // $email = $this->accountNo . '@curtin.edu.au';
        $email = $this->accountNo . '@test.com.au';
        return $email;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function routeNotificationFor($driver, $notification = null)
    {
        // $email = $this->accountNo . '@curtin.edu.au';
        $email = $this->accountNo . '@test.com.au';
        return $email;
    }

    public function getName()
    {
        $name = $this->fName . $this->lName;
        return $name;
    }
}
