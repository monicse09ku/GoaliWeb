<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
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

    public function email_check(Request $request){
        $verification_code = 123456;
        $emailData['email'] = $request->email;
        $emailData['subject'] = Common::SITE_TITLE." - Registration verification code";
        $emailData['code'] = $verification_code;
        $view = 'emails.registration_verification';
        $result = SendMails::sendMail($emailData, $view);
        if($result){
            return 'Email sent';
        }
        else{
            return 'Email not sent';
        }
    }

    public function login(Request $request)
    {
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
                'status' => ['active','pending'],
                'role' => 3,
            ]);

            if ($result) {
                $loggedUser = Auth::user();
                $user = User::select('users.id','users.name','users.email','users.phone','users.photo','users.role','users.oauth_token','users.status','users.email_verified_at','clients.id as client_id','clients.account_type','clients.allow_notification')
                    ->join('clients','clients.user_id','=','users.id')
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
     * LOgin with google, facebook, apple etc.
     * */
    public function otherLogin(Request $request)
    {
        if ($request->email == '') {
            return ['status'=>401, 'reason'=>'Email is required'];
        }
        if ($request->account_from == '') {
            return ['status'=>401, 'reason'=>'Account from is required'];
        }
        try {
            $user = User::select('users.id','users.name','users.email','users.phone','users.photo','users.role','users.oauth_token','users.status','users.email_verified_at','clients.id as client_id')
                ->join('clients','clients.user_id','=','users.id')
                ->where('users.email',$request->email)
                ->where('users.role',3)
                ->whereIn('users.status',['active','pending'])
                ->first();

            if (!empty($user)) {
                return ['status' => 200, 'reason' => 'Successfully Authenticated','user'=>$user,'role_id'=>$user->role];
            }
            else {
                DB::beginTransaction();

                $old_client = User::where('email',$request->email)->where('status','!=','deleted')->first();
                if(!empty($old_client)){
                    return ['status'=>401, 'reason'=>'This client email already exists'];
                }

                $token = Common::generaterandomString(8);

                $client = NEW Client();
                $client->first_name = $request->name;
                //$client->last_name = $request->last_name;
                $client->email = $request->email;
                $client->account_from = $request->account_from;
                $client->created_at = date('Y-m-d h:i:s');
                $client->save();

                /*
                 * Adding user information
                 * */
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->username = $request->email;
                $user->password = bcrypt($request->email);
                $user->phone = $request->phone;
                $user->role = 3;
                $user->oauth_token = $token;
                $user->email_verified_at = date('Y-m-d h:i:s');
                $user->status = 'pending';
                $user->save();

                /*
                 * Update client user id
                 * */
                $client_update = Client::where('id',$client->id)->first();
                $client_update->user_id = $user->id;
                $client_update->save();

                $user = User::select('users.id','users.name','users.email','users.phone','users.photo','users.role','users.oauth_token','users.status','clients.id as client_id')
                    ->join('clients','clients.user_id','=','users.id')
                    ->where('users.id',$user->id)
                    ->first();

                DB::commit();

                return ['status' => 200, 'reason' => 'Successfully Authenticated','user'=>$user,'role_id'=>$user->role];
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Forget password request
     * */
    public function forgetPasswordRequest(Request $request)
    {
        if ($request->email == '') {
            return ['status'=>401, 'reason'=>'Email address is required'];
        }

        try {
            $client = Client::where('email', $request->email)->first();
            if(empty($client)){
                return ['status' => 401,'reason' => 'Sorry! We did not find any client with this email address'];
            }

            $verification_code = Common::generaterandomNumber(5);

            $msg = "<html>
            <head>
            <title>Password reset code</title>
            </head>
            <body>
            <p>Your password reset code is <strong>".$verification_code."</strong> </p>
            <p>Use this code to reset your password.</p>
            <p>Thanks,</p>
            <p>GOALI</p>

            </body>
            </html>";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\b";
            $headers .= 'From: noreply@goali.com.co' . "\r\n";
            mail($client->email, Common::SITE_TITLE." - Registration verification code", $msg, $headers);

            /*
             * Confirmation code sending (sms) ends
             * */
            return ['status' => 200, 'reason' => 'We have sent you an email with a code, to re-set your password','client_id'=>$client->id, 'code'=>$verification_code];
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Reset password request
     * */
    public function resetPasswordRequest(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }

        try {
            $client = Client::where('id', $request->client_id)->first();

            $verification_code = Common::generaterandomNumber(5);

            $msg = "<html>
            <head>
            <title>Password reset code</title>
            </head>
            <body>
            <p>Your password reset code is <strong>".$verification_code."</strong> </p>
            <p>Use this code to reset your password.</p>
            <p>Thanks,</p>
            <p>GOALI</p>

            </body>
            </html>";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\b";
            $headers .= 'From: noreply@goali.com.co' . "\r\n";
            mail($client->email, Common::SITE_TITLE." - Registration verification code", $msg, $headers);

            /*
             * Confirmation code sending (sms) ends
             * */
            return ['status' => 200, 'reason' => 'We have sent you an email with a code, to re-set your password','client_id'=>$request->client_id, 'code'=>$verification_code];
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Reset password request
     * */
    public function resetPasswordConfirmation(Request $request)
    {
        /*if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }*/
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->password == '') {
            return ['status'=>401, 'reason'=>'Password is required'];
        }

        try {
            $client = Client::where('id', $request->client_id)->first();
            $user = User::find($client->user_id);
            $user->password = bcrypt($request->password);
            $user->updated_at = date('Y-m-d h:i:s');
            $user->save();

            return ['status' => 200, 'reason' => 'Password updated successfully','client_id'=>$request->client_id];
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Saving new client data from signup
     * */
    public function storeClient(Request $request)
    {
        /*if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }*/
        if ($request->first_name == '') {
            return ['status'=>401, 'reason'=>'First name is required'];
        }
        if ($request->email == '') {
            return ['status'=>401, 'reason'=>'Email is required'];
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
                return ['status' => 200,'reason' => 'Successfully saved','token'=>$result['token'],'user'=>$result['user']];
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
     * Resending verification code
     * */
    public function resendVerificationCode(Request $request)
    {
        /*if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }*/
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }

        try {
            $result = Service::resendVerificationCode($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Verification code successfully sent. Check your email.'];
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
        /*if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }*/
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::getClientDetails($request->id);
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
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        /*if ($request->first_name == '') {
            return ['status'=>401, 'reason'=>'First name is required'];
        }*/
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


    /*
     * Saving new client photo
     * */
    public function updateClientPhoto(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->photo == '') {
            return ['status'=>401, 'reason'=>'Photo is required'];
        }
        try {
            $result = Service::updateClientPhoto($request);
            return $result;
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }


    /*
     * Verifying client and user
     * */
    public function verifyClient(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->verification_code == '') {
            return ['status'=>401, 'reason'=>'Verification code is required'];
        }
        try {
            $result = Service::verifyClient($request);
            return $result;
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Update client type
     * */
    public function updateClientType(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->account_type == '') {
            return ['status'=>401, 'reason'=>'Account type is required'];
        }
        try {
            $result = Service::updateClientType($request);
            return $result;
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Update client notification settings
     * */
    public function updateClientNotificationSetting(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->allow_notification == '') {
            return ['status'=>401, 'reason'=>'Allow notification is required'];
        }
        try {
            $result = Service::updateClientNotificationSetting($request);
            return $result;
        }
        catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401,'reason' => $e->getMessage()];
        }
    }

    /*
     * Getting all genre data
     * */
    public function allGenre(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }

        try {
            $result = Service::getAllGenre();
            if($result['status']==200){
                return ['status' => 200,'data' => $result['data']];
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
     * Getting all goal data
     * */
    public function allGoal(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }

        try {
            $result = Service::getAllGoal($request->client_id);
            if($result['status']==200){
                return ['status' => 200,'data' => $result['data']];
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
     * Saving new goal data
     * */
    public function storeGoal(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->goal_type == '') {
            return ['status'=>401, 'reason'=>'Goal type is required'];
        }
        if ($request->goal_name == '') {
            return ['status'=>401, 'reason'=>'Goal name is required'];
        }

        try {
            $result = Service::storeGoal($request);
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
     * Getting goal detail data
     * */
    public function getGoalDetails(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'goal id is required'];
        }
        try {
            $result = Service::getGoalDetails($request->goal_id);
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
     * Updating goal data
     * */
    public function updateGoal(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'goal id is required'];
        }
        if ($request->goal_type == '') {
            return ['status'=>401, 'reason'=>'Goal type is required'];
        }
        if ($request->goal_name == '') {
            return ['status'=>401, 'reason'=>'Goal name is required'];
        }
        try {
            $result = Service::updateGoal($request);
            return $result;
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

    /*
     * Deleting goal data
     * */
    public function deleteGoal(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'goal id is required'];
        }
        try {
            $result = Service::deleteGoal($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Successfully deleted'];
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
     * Making goal complete
     * */
    public function makeCompleteGoal(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'Goal id is required'];
        }
        try {
            $result = Service::makeCompleteGoal($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Goal successfully completed'];
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
     * Saving new goal step data
     * */
    public function storeGoalStep(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'Goal id is required'];
        }
        if ($request->step_name == '') {
            return ['status'=>401, 'reason'=>'Step name is required'];
        }
        if ($request->end_date == '') {
            return ['status'=>401, 'reason'=>'End date is required'];
        }

        try {
            $result = Service::storeGoalStep($request);
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
     * Getting goal step detail data
     * */
    public function getGoalStepDetails(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->step_id == '') {
            return ['status'=>401, 'reason'=>'Step id is required'];
        }
        try {
            $result = Service::getGoalStepDetails($request->step_id);
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
     * Updating goal step data
     * */
    public function updateGoalStep(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->step_id == '') {
            return ['status'=>401, 'reason'=>'Step id is required'];
        }
        if ($request->step_name == '') {
            return ['status'=>401, 'reason'=>'Step name is required'];
        }
        try {
            $result = Service::updateGoalStep($request);
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

    /*
     * Deleting goal step data
     * */
    public function deleteGoalStep(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->step_id == '') {
            return ['status'=>401, 'reason'=>'Step id is required'];
        }
        try {
            $result = Service::deleteGoalStep($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Successfully deleted'];
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
     * Requesting goal step mark off
     * */
    public function requestGoalStepMarkOff(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->step_id == '') {
            return ['status'=>401, 'reason'=>'Step id is required'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::requestGoalStepMarkOff($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Goal step mark off request successfully sent'];
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
     * Making goal step complete
     * */
    public function makeCompleteGoalStep(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->step_id == '') {
            return ['status'=>401, 'reason'=>'Step id is required'];
        }
        try {
            $result = Service::makeCompleteGoalStep($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Goal step successfully completed'];
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
     * Deleting goal step attachment
     * */
    public function deleteStepAttachment(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->attachment_id == '') {
            return ['status'=>401, 'reason'=>'Attachment id is required'];
        }
        try {
            $result = Service::deleteStepAttachment($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => $result['reason']];
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
     * Getting collaborative goal steps
     * */
    public function getCollaborativeStep(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'Goal id is required'];
        }
        if ($request->collaborator_id == '') {
            return ['status'=>401, 'reason'=>'Collaborator id is required'];
        }
        try {
            $result = Service::getCollaborativeStep($request);
            if($result['status']==200){
                return $result;
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
     * Search goal/step
     * */
    public function search(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->search_category == '') {
            return ['status'=>401, 'reason'=>'Search category is required'];
        }
        if ($request->text == '') {
            return ['status'=>401, 'reason'=>'Text is is required'];
        }
        if ($request->search_category == 'goal' && $request->client_id=='') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }

        try {
            $result = Service::search($request);
            return $result;
            if($result['status']==200){
                if($request->search_category=='goal') {
                    return ['status' => 200, 'goals' => $result['goals'], 'goal_steps' => $result['goal_steps']];
                }
                else if($request->search_category=='people') { // If search category is people
                    return ['status' => 200, 'clients' => $result['clients']];
                }
                else if($request->search_category=='skill') { // If search category is skill
                    return ['status' => 200, 'clients' => $result['clients']];
                }
                else{
                    //
                }
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
     * Search my current goal
     * */
    public function currentGoalSearch(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id=='') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->goal_type == '') {
            return ['status'=>401, 'reason'=>'Goal type is required'];
        }
        /*if ($request->text == '') {
            return ['status'=>401, 'reason'=>'Text is required'];
        }*/

        try {
            $result = Service::currentGoalSearch($request);
            if($result['status']==200){
                return ['status' => 200, 'data' => $result['data']];
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
     * Adding goal collaborators
     * */
    public function addCollaborators(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'goal id is required'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'client id is required'];
        }
        if ($request->collaborators == '') {
            return ['status'=>401, 'reason'=>'collaborators is required'];
        }
        try {
            $result = Service::addCollaborators($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Collaborators added successfully'];
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
     * Getting collaborator detail data
     * */
    public function getCollaboratorProfile(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->collaborator_id == '') {
            return ['status'=>401, 'reason'=>'Collaborator id is required'];
        }
        try {
            $result = Service::getCollaboratorProfile($request->collaborator_id);
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
     * Removing goal collaborators
     * */
    public function deleteCollaborators(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->goal_id == '') {
            return ['status'=>401, 'reason'=>'goal id is required'];
        }
        if ($request->collaborator_id == '') {
            return ['status'=>401, 'reason'=>'collaborator id is required'];
        }
        try {
            $result = Service::deleteCollaborators($request);
            if($result['status']==200){
                return ['status' => 200,'reason' => 'Collaborators removed successfully'];
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
     * Getting clients completed goal details
     * */
    public function getTrophies(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::getTrophies($request);
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
     * Searching clients completed goal
     * */
    public function searchCompletedGoal(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::searchCompletedGoal($request);
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
     * Get all notification for the client
     * */
    public function getNotifications(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::getNotifications($request);
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
     * Get notification details
     * */
    public function getNotificationDetails(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->notification_id == '') {
            return ['status'=>401, 'reason'=>'Notification id is required'];
        }
        try {
            $result = Service::getNotificationDetails($request->notification_id);
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
     * Update notification details
     * */
    public function updateNotification(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->notification_id == '') {
            return ['status'=>401, 'reason'=>'Notification id is required'];
        }
        try {
            $result = Service::updateNotification($request);
            if($result['status']==200){
                return ['status' => 200,'reason'=>$result['reason']];
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
     * add network connection
     * */
    public function getMyNetworkConnection(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::getMyNetworkConnection($request);
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
     * add network connection
     * */
    public function addNetworkConnection(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        if ($request->connected_with_id == '') {
            return ['status'=>401, 'reason'=>'Connected with id is required'];
        }
        try {
            $result = Service::addNetworkConnection($request);
            if($result['status']==200){
                return ['status' => 200,'data'=>$result['reason']];
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
     * view network connection
     * */
    public function viewNetworkConnection(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->network_id == '') {
            return ['status'=>401, 'reason'=>'Network id is required'];
        }
        try {
            $result = Service::viewNetworkConnection($request->network_id);
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
     * Accept network connection
     * */
    public function acceptNetworkConnection(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->network_id == '') {
            return ['status'=>401, 'reason'=>'Network id is required'];
        }
        try {
            $result = Service::acceptNetworkConnection($request->network_id);
            if($result['status']==200){
                return ['status' => 200,'reason'=>$result['reason']];
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
     * Decline network connection
     * */
    public function declineNetworkConnection(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->network_id == '') {
            return ['status'=>401, 'reason'=>'Network id is required'];
        }
        if ($request->notification_id == '') {
            return ['status'=>401, 'reason'=>'Notification id is required'];
        }
        try {
            $result = Service::declineNetworkConnection($request);
            if($result['status']==200){
                return ['status' => 200,'reason'=>$result['reason']];
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
     * Remove network connection
     * */
    public function removeNetworkConnection(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->network_id == '') {
            return ['status'=>401, 'reason'=>'Network id is required'];
        }
        try {
            $result = Service::removeNetworkConnection($request);
            if($result['status']==200){
                return ['status' => 200,'reason'=>$result['reason']];
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
     * Getting support tickets
     * */
    public function getTickets(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->client_id == '') {
            return ['status'=>401, 'reason'=>'Client id is required'];
        }
        try {
            $result = Service::getTickets($request);
            if($result['status']==200){
                return $result;
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
     * Getting support ticket details
     * */
    public function getTicketDetails(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->ticket_id == '') {
            return ['status'=>401, 'reason'=>'Ticket id is required'];
        }
        try {
            $result = Service::getTicketDetails($request);
            if($result['status']==200){
                return $result;
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
     * Saving support ticket
     * */
    public function storeSupportTicket(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->name == '') {
            return ['status'=>401, 'reason'=>'Name is required'];
        }
        if ($request->email == '') {
            return ['status'=>401, 'reason'=>'Email is required'];
        }
        if ($request->message == '') {
            return ['status'=>401, 'reason'=>'Message is required'];
        }
        try {
            $result = Service::storeSupportTicket($request);
            if($result['status']==200){
                return ['status' => 200,'reason'=>$result['reason']];
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
     * Saving support ticket reply
     * */
    public function storeSupportTicketReply(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->ticket_id == '') {
            return ['status'=>401, 'reason'=>'Ticket id is required'];
        }
        if ($request->name == '') {
            return ['status'=>401, 'reason'=>'Name is required'];
        }
        if ($request->email == '') {
            return ['status'=>401, 'reason'=>'Email is required'];
        }
        if ($request->message == '') {
            return ['status'=>401, 'reason'=>'Message is required'];
        }
        try {
            $result = Service::storeSupportTicketReply($request);
            if($result['status']==200){
                return ['status' => 200,'reason'=>$result['reason']];
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
