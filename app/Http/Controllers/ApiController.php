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
                'status' => ['active'],
                'role' => 3,
            ]);

            if ($result) {
                $loggedUser = Auth::user();
                $user = User::select('id','name','email','phone','photo','role','oauth_token')
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
        /*if ($request->password == '') {
            return ['status'=>401, 'reason'=>'Password is required'];
        }*/

        try {
            $client = Client::where('id', $request->client_id)->first();
            /*
             * Confirmation code sending (sms) start
             * */
             $code = 123456;
            $emailData['email'] = $client->email;
            $emailData['subject'] = Common::SITE_TITLE." - Password reset code";
            $emailData['code'] = $code;
            $view = 'emails.password_reset_code';
            $result = SendMails::sendMail($emailData, $view);
            /*
             * Confirmation code sending (sms) ends
             * */
            if($result){
                return ['status' => 200, 'reason' => 'We have sent you an email with a code, to re-set your password','client_id'=>$request->client_id, 'code'=>$code];
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
     * Reset password request
     * */
    public function resetPasswordConfirmation(Request $request)
    {
        if ($request->oAuth_token != Common::OAUTH_TOKEN) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
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
                return ['status' => 200,'reason' => 'Successfully saved','id'=>$result['id'],'token'=>$result['token']];
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
     * Search goal/step
     * */
    public function search(Request $request)
    {
        if (!Service::hasAccess($request->oAuth_token)) {
            return ['status'=>401, 'reason'=>'Invalid oAuth token'];
        }
        if ($request->search_category == '') {
            return ['status'=>401, 'reason'=>'Search category is is required'];
        }
        if ($request->text == '') {
            return ['status'=>401, 'reason'=>'Text is is required'];
        }
        try {
            $result = Service::search($request);
            if($result['status']==200){
                if($request->search_category=='goal') {
                    return ['status' => 200, 'goals' => $result['goals'], 'goal_steps' => $result['goal_steps']];
                }
                else{ // If search category is people
                    return ['status' => 200, 'clients' => $result['clients']];
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
}
