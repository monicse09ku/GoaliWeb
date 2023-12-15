<?php

namespace App;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Session;
use Auth;
use DB;

/**
 * Class Service, this class is to use project common service functions
 *
 * @package App
 */
class Service
{
    const OAUTH_TOKEN = 'goali123456';

    public static function is_admin_login(){
        if (Session::get('user_id') && (Session::get('role')==0 || Session::get('role')==1 || Session::get('role')==2)) {
            return 1;
        }
        return 0;
    }

    public static function is_user_login(){
        if (Session::get('user_id')) {
            return 1;
        }
        return 0;
    }

    /*
     * Get notifications
     * */
    public static function getNotifications($user_id=''){
        if($user_id == ''){
            $user = Auth::user();
            if($user->role==7 || $user->role==8 || $user->role==9){ // If user is DGH, DGH viewer or DGH monitor
                $user_id = Service::getParentZhc($user->id);
            }
            else{
                $user_id = $user->id;
            }
        }

        $notifications = Notification::select('notifications.*','notification_from.name as notification_from')
            ->leftJoin('users as notification_from','notification_from.id','=','notifications.notification_from_id')
            ->where('notification_to_id',$user_id)
            ->orderBy('notifications.id','DESC')
            ->get();
        return $notifications;
    }

    public static function getNotificationDetails($notification_id=''){
        $notifications = Notification::select('notifications.*','notification_from.name as notification_from')
            ->leftJoin('users as notification_from','notification_from.id','=','notifications.notification_from_id')
            ->where('notifications.id',$notification_id)
            ->orderBy('notifications.id','DESC')
            ->get();
        return $notifications;
    }

    public static function getUnreadNotifications($user_id=''){
        if($user_id == ''){
            $user = Auth::user();
            if($user->role==7 || $user->role==8 || $user->role==9){ // If user is DGH, DGH viewer or DGH monitor
                $user_id = Service::getParentZhc($user->id);
            }
            else{
                $user_id = $user->id;
            }
        }

        $notifications = Notification::select('notifications.*','notification_from.name as notification_from')
            ->leftJoin('users as notification_from','notification_from.id','=','notifications.notification_from_id')
            ->where('notification_to_id',$user_id)
            ->where('is_read',0)
            ->orderBy('notifications.id','DESC')
            ->get();
        return $notifications;
    }

    /*
     * Saving household data
     * */
    public static function storeClient($request){
        try{
            DB::beginTransaction();

            $old_client = User::where('username',$request->username)->where('status','!=','deleted')->first();
            if(!empty($old_client)){
                return ['status'=>401, 'reason'=>'This client username already exists'];
            }

            $client = NEW Client();
            $client->first_name = $request->first_name;
            $client->last_name = $request->last_name;
            $client->email = $request->email;

            if(isset($request->about_me)) {
                $client->about_me = $request->about_me;
            }
            if(isset($request->hobbies) && $request->hobbies != ''){
                $client->hobbies = implode(',',$request->hobbies);
            }
            if(isset($request->language) && $request->language != ''){
                $client->languages = implode(',',$request->language);
            }
            if(isset($request->core_skills) && $request->core_skills != ''){
                $client->core_skills = implode(',',$request->core_skills);
            }

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

            return ['status'=>200, 'id'=>$client->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting client details
     * */
    public static function getClientDetails($client_id){
        try{
            $client = Client::select('clients.*')->where('clients.id',$client_id)->first();

            return ['status'=>200, 'data'=>$client];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Saving client data
     * */
    public static function updateClient($request){
        try{
            $client = Client::where('id',$request->client_id)->first();
            if(isset($request->first_name)){
                $client->first_name = $request->first_name;
            }
            if(isset($request->last_name)) {
                $client->last_name = $request->last_name;
            }
            if(isset($request->email)) {
                $client->email = $request->email;
            }
            if(isset($request->about_me)) {
                $client->about_me = $request->about_me;
            }
            if(isset($request->hobbies) && $request->hobbies != ''){
                $client->hobbies = implode(',',$request->hobbies);
            }
            if(isset($request->language) && $request->language != ''){
                $client->languages = implode(',',$request->language);
            }
            if(isset($request->core_skills) && $request->core_skills != ''){
                $client->core_skills = implode(',',$request->core_skills);
            }
            $client->updated_at = date('Y-m-d h:i:s');
            $client->save();

            return ['status'=>200, 'id'=>$client->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }


}//End
