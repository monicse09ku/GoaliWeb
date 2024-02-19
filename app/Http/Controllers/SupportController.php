<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
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
            $tickets = SupportTicket::where('status','active')
                ->orderBy('id','desc')
                ->paginate();
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
            $ticket = SupportTicket::where('id',$request->id)
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
            return redirect('error_404');
        }
    }
}
