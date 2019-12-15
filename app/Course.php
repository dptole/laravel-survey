<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'courses';

    protected $fillable = [
        'course_code', 'course_title','prgram_id','department_id', 'status',
    ];
    
    public function program()
    {
        return $this->belongsTo('App\Program');
    }
    
    public function department()
    {
        return $this->belongsTo('App\Department');
    }
}
