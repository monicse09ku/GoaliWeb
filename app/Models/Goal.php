<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Goal extends Model
{
    protected $guarded = [];

    public function steps()
    {
        $instance = $this->hasMany('App\Models\GoalStep','goal_id','id');
        $instance = $instance->where('goal_steps.status','active');
        return $instance;
    }
    public function collaborators()
    {
        $instance = $this->hasMany('App\Models\GoalCollaborator','goal_id','id');
        //$instance = $instance->select('clients.id','clients.first_name','clients.last_name');
        $instance = $instance->leftJoin('clients','clients.id','=','goal_collaborators.collaborator_id');
        return $instance;
    }
}
