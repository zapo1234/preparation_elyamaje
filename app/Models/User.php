<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'role_id',
        'password',
        'poste',
        'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    public function roles(){
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole($role){
        return $this->roles->where('id', $role)->count() > 0;
    }
    
    public function hasAnyRole($roles){
        foreach($roles as $role){
            if($this->hasRole($role)){
                return true;
            }
         }
         return false;
    }

    public static function findByEmail($email){
        return static::where('email', $email)->first();
    }

}
