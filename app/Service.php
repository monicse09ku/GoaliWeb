<?php

namespace App;
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


}//End
