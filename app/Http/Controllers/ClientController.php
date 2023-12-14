<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use App\Common;
use Auth;
use File;
use Session;
use DB;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        try{
            $clients = Client::select('clients.*');
            $clients = $clients->whereIn('clients.status',['active','inactive']);
            if($request->name != ''){
                $clients = $clients->where('clients.first_name','like','%'.$request->name.'%');
            }
            if($request->phone != ''){
                $clients = $clients->where('clients.phone',$request->phone);
            }
            $clients = $clients->paginate(50);
            return view('client.index',compact('clients'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function create(Request $request)
    {
        try{
            return view('client.create');
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function store(Request $request)
    {
        try{
            $user = Auth::user();

            DB::beginTransaction();

            $old_client = User::where('username',$request->username)->where('status','!=','deleted')->first();
            if(!empty($old_client)){
                return ['status'=>401, 'reason'=>'This client username already exists'];
            }

            $client = NEW Client();
            $client->first_name = $request->first_name;
            $client->last_name = $request->last_name;
            $client->email = $request->email;
            $client->about_me = $request->about_me;
            $client->hobbies = implode(',',$request->hobbies);
            $client->languages = implode(',',$request->language);
            $client->core_skills = implode(',',$request->core_skills);
            $client->created_at = date('Y-m-d h:i:s');
            $client->save();

            /*
             * Adding user information
             * */
            $user = new User();
            $user->name = $request->first_name." ".$request->last_name;
            $user->email = $request->email;
            $user->username = $request->username;
            $user->password = bcrypt($request->password);
            $user->phone = $request->phone;
            $user->role = 3;
            $user->status = 'active';
            $user->save();

            /*
             * Update client user id
             * */
            $client_update = Client::where('id',$client->id)->first();
            $client_update->user_id = $user->id;
            $client_update->save();

            Db::commit();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            DB::rollback();
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }

    public function edit(Request $request)
    {
        try{
            $client = Client::select('clients.*')
                ->where('clients.id',$request->id)
                ->first();
            return view('client.edit',compact('client'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function update(Request $request)
    {
        try{
            $user = Auth::user();

            $client = Client::where('id',$request->id)->first();
            $client->first_name = $request->first_name;
            $client->last_name = $request->last_name;
            $client->email = $request->email;
            $client->about_me = $request->about_me;
            $client->hobbies = implode(',',$request->hobbies);
            $client->languages = implode(',',$request->language);
            $client->core_skills = implode(',',$request->core_skills);
            $client->updated_at = date('Y-m-d h:i:s');
            $client->save();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }

    public function delete(Request $request)
    {
        try{
            $user = Auth::user();

            $client = Client::where('id',$request->client_id)->first();
            $client->status = 'deleted';
            $client->deleted_by = $user->id;
            $client->deleted_at = date('Y-m-d h:i:s');
            $client->save();

            return ['status'=>200, 'reason'=>'Successfully deleted'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }
}
