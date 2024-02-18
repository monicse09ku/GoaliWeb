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
                $user = User::select('users.id','users.name','users.email','users.phone','users.photo','users.role','users.oauth_token','clients.id as client_id')
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
        $files = json_decode($request->files, true);
        echo "<pre>"; print_r($files); echo "</pre>"; exit();
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
            var_dump($result);
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
}
