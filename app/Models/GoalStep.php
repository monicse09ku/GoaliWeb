<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalStep extends Model
{
    protected $guarded = [];

    public function collaborators()
    {
        $instance = $this->hasMany('App\Models\StepCollaborator','step_id','id');
        //$instance = $instance->select('clients.id','clients.first_name','clients.last_name');
        $instance = $instance->leftJoin('clients','clients.id','=','step_collaborators.collaborator_id');
        return $instance;
    }
}
