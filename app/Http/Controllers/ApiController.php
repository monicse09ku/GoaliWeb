<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Service;
use App\Common;
use App\SendMails;
use Session;
use Auth;
use DB;

class ApiController extends Controller
{
    public function __construct()
    {
        //
    }

    public function login(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->email == '') {
            return ['status'=>401, 'reason'=>'Email is required'];
        }
        if ($request->password == '') {
            return ['status'=>401, 'reason'=>'Password is required'];
        }
        try {
            $result = Auth::attempt([
                'username' => trim($request->username),
                'password' => $request->password,
                'status' => ['active'],
                'role' => 3,
            ]);

            if ($result) {
                $loggedUser = Auth::user();
                $user = User::select('users.*')
                    ->where('users.id',$loggedUser->id)
                    ->first();

                return ['status' => 200, 'reason' => 'Successfully Authenticated','user'=>$user,'role_id'=>$user->role];
            } else {
                return ['status' => 401, 'reason' => 'Invalid credentials'];
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Storing chw data
     * */
    public function storeChw(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->cc_id == '') {
            return ['status'=>401, 'reason'=>'CC id is required'];
        }
        if ($request->name == '') {
            return ['status'=>401, 'reason'=>'Name is required'];
        }
        /*if ($request->email == '') {
            return ['status'=>401, 'reason'=>'Email is required'];
        }
        if ($request->password == '') {
            return ['status'=>401, 'reason'=>'Password is required'];
        }*/

        $name_data = explode(' ',$request->name);
        $email = $name_data[0].Common::generaterandomNumber(1).'@gmail.com';
        $password = Common::generaterandomNumber(6);

        try {
            $chw = NEW User();
            $chw->parent_id = $request->cc_id;
            $chw->name = $request->name;
            $chw->email = $email;
            //$chw->password = bcrypt($password); // Password will be added from approval function
            $chw->phone = $request->phone;
            $chw->address = $request->address;
            $chw->status = 'pending';
            $chw->role = 4;
            // $chw->created_at = date('Y-m-d h:i:s');
            $chw->save();

            return ['status' => 200,'reason'=>'You have successfully registered'];
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Getting chw data
     * */
    public function getChws(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }

        try {
            if($request->cc_id !=''){
                $cc_ids = Service::getCcUserParent($request->cc_id); // Here $request->cc_id is the user id
            }
            else{
                $cc_ids = '';
            }
            $result = Service::getChws($cc_ids);
            if($result['status']==200){
                return ['status' => 200,'data'=>$result['data']];
            }
            else{
                return ['status' => 401,'reason' => $result['reason']];
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }
}
