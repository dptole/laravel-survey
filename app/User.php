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
     * Wrapper for the uuid.
     */
    public function save(array $options = [])
    {
        if (
          isset($this->attributes['uuid']) &&
          is_string($this->attributes['uuid']) &&
          Uuid::validate($this->attributes['uuid'])
        ) {
            $uuid = $this->attributes['uuid'];
        } else {
            $uuid = Uuid::generate(4);
        }

        $this->uuid = $uuid;

        return parent::save($options);
    }
}
