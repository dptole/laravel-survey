<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'programs';

    protected $fillable = [
        'name', 'department_id', 'status',
    ];
    
	public function department()
    {
        return $this->belongsTo('App\Department');
    }
}
