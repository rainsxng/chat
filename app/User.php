<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function isAdmin()
    {
        return  $this->role === 'admin';
    }

    public static function getRandColor() :string
    {
        $colors = ['#ed2213', '#2753e6',  '#7a55f2', '#9e03ff', '#050008', '#6e0c4b', '#0c97ed', '#4e5254', '#0d5e2a',  '#8a36a3', '#2f9c4a', '#a16d06'];
        $randIndex = array_rand($colors);
        return $colors[$randIndex];
    }
}
