<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Webpatser\Uuid\Uuid;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @param array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @param array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Auto generates the `uuid` field for the new User.
     *
     * @param options List of options. https://laravel.com/api/6.x/Illuminate/Database/Eloquent/Model.html#method_save
     * @return User
     */
    public function save(array $options = [])
    {
        $this->uuid = Uuid::generate(4);

        return parent::save($options);
    }
}
