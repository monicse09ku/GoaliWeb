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
        if ($request->username == '') {
            return ['status'=>401, 'reason'=>'Username is required'];
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
     * Saving new client data
     * */
    public function storeClient(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->first_name == '') {
            return ['status'=>401, 'reason'=>'First name is required'];
        }
        if ($request->username == '') {
            return ['status'=>401, 'reason'=>'Username is required'];
        }
        if ($request->password == '') {
            return ['status'=>401, 'reason'=>'Password is required'];
        }


        try {
            $result = Service::storeClient($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Successfully saved','id'=>$result['id']];
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

    /*
     * Getting client detail data
     * */
    public function getClientDetails(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::getClientDetails($request->client_id);
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

    /*
     * Saving new client data
     * */
    public function updateClient(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->first_name == '') {
            return ['status'=>401, 'reason'=>'First name is required'];
        }
        try {
            $result = Service::updateClient($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Successfully updated','id'=>$result['id']];
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
