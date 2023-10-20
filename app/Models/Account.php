<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notification;
use App\Notifications\NewMessages;
use Symfony\Component\Mailer\Exception\TransportException;

class Account extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    // set accountNo as primary key so eloquent doesn't have a fit about columns
    protected $primaryKey = 'accountNo';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'accountNo',
        'accountType',
        'lName',
        'fName',
        'password',
        'superiorNo',
        'schoolId', // SchoolID: 1 reserved for Super Administrator
        'isTemporaryManager'
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

    // needs to be named specifically this because of a call deep in the framework
    public function getEmailForPasswordReset()
    {
        $email = $this->accountNo . '@curtin.edu.au';
        return $email;
    }

    public function getEmail()
    {
        $email = $this->accountNo . '@curtin.edu.au';
        return $email;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function sendDailyMessageNotification($messages)
    {
        try{
            $this->notify(new NewMessages($messages, $this->isManager()));
            return true;
        }
        catch(TransportException $e)
        {
            return false;
        }
    }

    public function routeNotificationFor($driver, $notification = null)
    {
        $email = $this->accountNo . '@curtin.edu.au';
        return $email;
    }

    public function getName()
    {
        $name = $this->fName . " " . $this->lName;
        return $name;
    }

    public function isManager()
    {
        $isManager = true;
        if ($this->accountType == "staff") {
            $isManager = false;
        }
        return $isManager;
    }
}
