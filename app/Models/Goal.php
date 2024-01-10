<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $guarded = [];

    public function steps()
    {
        $instance = $this->hasMany('App\Models\GoalStep','goal_id','id');
        return $instance;
    }
}
