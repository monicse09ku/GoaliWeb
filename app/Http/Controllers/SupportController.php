<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Common;
use Auth;
use Session;
use DB;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application tickets.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function tickets(Request $request)
    {
        try{
            $tickets = SupportTicket::whereIn('status',['active','closed']);
            if($request->is_read != ''){
                if($request->is_read=='Unread'){
                    $tickets =$tickets->where('is_read',0);
                }
                else if($request->is_read=='Read'){
                    $tickets =$tickets->where('is_read',1);
                }
            }
            $tickets =$tickets->orderBy('id','desc');
            $tickets =$tickets->paginate();
            return view('support_ticket.index', compact('tickets'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    /**
     * Show ticket details
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function ticketDetails(Request $request)
    {
        try{
            $ticket = SupportTicket::with('replies')
                ->where('id',$request->id)
                ->first();
            $ticket->is_read = 1;
            $ticket->save();

            return view('support_ticket.details', compact('ticket'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    /**
     * Delete ticket details
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function storeTicketReply(Request $request)
    {
        try{
            $reply = NEW SupportTicketReply();
            $reply->ticket_id = $request->ticket_id;
            $reply->sender_type = 'admin';
            $reply->receiver_type = 'client';
            $reply->name = $request->name;
            $reply->message = $request->message;
            $reply->created_at = date('Y-m-d h:i:s');
            $reply->save();

            return ['status'=>200, 'reason'=>'Successfully sent'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later'];
        }
    }

    /**
     * Close ticket details
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function closeTicket(Request $request)
    {
        try{
            $ticket = SupportTicket::where('id',$request->ticket_id)->first();
            $ticket->status = 'closed';
            $ticket->closed_at = date('Y-m-d h:i:s');
            $ticket->save();

            return ['status'=>200, 'reason'=>'Successfully closed'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later'];
        }
    }

    /**
     * Delete ticket details
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function deleteTicket(Request $request)
    {
        try{
            $ticket = SupportTicket::where('id',$request->ticket_id)->first();
            $ticket->status = 'deleted';
            $ticket->deleted_at = date('Y-m-d h:i:s');
            $ticket->save();

            return ['status'=>200, 'reason'=>'Successfully deleted'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later'];
        }
    }
}
