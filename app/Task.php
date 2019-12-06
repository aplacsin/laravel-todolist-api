<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'parent_id', 'title', 'description', 'priority', 'status', 'completed_at'
    ];

    public function task()
{
    return $this->hasMany('App\Task', 'parent_id');
}

public function subtask()
{
    return $this->hasMany('App\Task', 'parent_id')->with('subtask');
}
    
}
