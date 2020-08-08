<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\Address;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'gender', 'phone_number', 'avatar', 'date_of_birth'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Set the user's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Get the permanent address associated with the user.
     */
    public function AauthAcessToken(){
        return $this->hasMany('\App\OauthAccessToken');
    }
    
    public function permanentAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')
            ->where('type', 'permanent')->select(['user_id', 'street', 'city', 'state', 'country', 'pincode']);
    }

    /**
     * Get the company address associated with the user.
     */
    public function companyAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')
            ->where('type', 'company')->select(['user_id', 'street', 'city', 'state', 'country', 'pincode']);
    }

    public function addresses()
    {
        return $this->hasOne(Address::class, 'user_id', 'id');
    }
}
