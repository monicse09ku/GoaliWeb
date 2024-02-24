<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SupportTicket extends Model
{
    protected $guarded = [];

    public function replies()
    {
        $instance = $this->hasMany('App\Models\SupportTicketReply','ticket_id','id');
        $instance = $instance->where('support_ticket_replies.status','active');
        $instance = $instance->orderBy('support_ticket_replies.id','desc');
        return $instance;
    }
}
