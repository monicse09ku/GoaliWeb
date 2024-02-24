<?php

namespace App;
use App\Models\Client;
use App\Models\Genre;
use App\Models\Goal;
use App\Models\GoalStep;
use App\Models\GoalCollaborator;
use App\Models\GoalStepAttachment;
use App\Models\StepCollaborator;
use App\Models\Notification;
use App\Models\Network;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\SendMails;
use Session;
use Auth;
use DB;
use File;

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

    public static function hasAccess($token){
        if($token==''){
            return 0;
        }
        $user = User::where('oauth_token',$token)->first();
        if(!empty($user)){
            return 1;
        }
        else{
            return 0;
        }
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
     * Saving client data
     * */
    public static function storeClient($request){
        try{
            DB::beginTransaction();

            $old_client = User::where('username',$request->username)->where('status','!=','deleted')->first();
            if(!empty($old_client)){
                return ['status'=>401, 'reason'=>'This client username already exists'];
            }

            $old_client = User::where('email',$request->email)->where('status','!=','deleted')->first();
            if(!empty($old_client)){
                return ['status'=>401, 'reason'=>'This client email already exists'];
            }

            $token = Common::generaterandomString(8);
            $verification_code = Common::generaterandomNumber(5);

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
            $user->oauth_token = $token;
            $user->verification_code = $verification_code;
            $user->status = 'pending';
            $user->save();

            /*
             * Confirmation code sending (sms) start
             * */
            /*$emailData['email'] = $client->email;
            $emailData['subject'] = Common::SITE_TITLE." - Registration verification code";
            $emailData['code'] = $verification_code;
            $view = 'emails.registration_verification';
            $result = SendMails::sendMail($emailData, $view);*/

            $msg = "<html>
            <head>
            <title>Registration Verification</title>
            </head>
            <body>
            <p>Your registration verification code is <strong>".$verification_code."</strong> </p>
            <p>Use this code to verify your registration.</p>
            <p>Thanks,</p>
            <p>GOALI</p>

            </body>
            </html>";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\b";
            $headers .= 'From: noreply@goali.com.co' . "\r\n";
            mail($client->email, Common::SITE_TITLE." - Registration verification code", $msg, $headers);

            /*
             * Verification code sending (sms) ends
             * */

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

            Db::commit();

            return ['status'=>200, 'reason'=>'We have sent you an email with a code, to verify your registration', 'token'=>$token, 'user'=>$user];
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
            if(isset($request->email)) {
                $old_client = Client::where('email', $request->email)->where('status', '!=', 'deleted')->first();
                if (!empty($old_client) && $old_client->id != $request->client_id) {
                    return ['status' => 401, 'reason' => 'This client email already exists'];
                }
            }

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
            $client->hobbies = $request->hobbies;
            $client->languages = $request->language;
            $client->core_skills = $request->core_skills;
            $client->updated_at = date('Y-m-d h:i:s');
            $client->save();

            return ['status'=>200, 'id'=>$client->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }


    /*
     * Saving client photo
     * */
    public static function updateClientPhoto($request){
        try{
            $user = User::select('users.*')
                ->join('clients','clients.user_id','users.id')
                ->where('clients.id',$request->client_id)
                ->first();

            /*
             * Uploading and updating clients profile photo
             * */
            $file_name = $user->username."-".time().".png";
            $uri_path = "uploads/users/" . $file_name;
            $full_path = public_path() .'/'. $uri_path;
            $img = $request->photo;
            // $img = substr($img, strpos($img, ",")+1);
            $data = base64_decode($img);
            $success = file_put_contents($full_path, $data);
            if($success){
                // Updating photo path to users table
                $user->photo = $uri_path;
                $user->updated_at = date('Y-m-d h:i:s');
                $user->save();

                // Updating photo path to clients table
                $client = Client::where('id',$request->client_id)->first();
                $client->photo = $uri_path;
                $client->updated_at = date('Y-m-d h:i:s');
                $client->save();
                return ['status'=>200, 'reason'=>'Successfully saved', 'file_path'=>$uri_path];
            }
            else{
                return ['status'=>401, 'reason'=>'Unable to save the file.'];
            }

        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Verifying client and user
     * */
    public static function verifyClient($request){
        try{
            $user = User::select('users.*')
                ->join('clients','clients.user_id','users.id')
                ->where('clients.id',$request->client_id)
                ->where('users.verification_code',$request->verification_code)
                ->first();

            if(empty($user)){
                return ['status'=>401, 'reason'=>'Invalid verification code'];
            }

            //Updating user verification data
            $user->email_verified_at = date('Y-m-d h:i:s');
            $user->verification_code = '';
            $user->status = 'active';
            $user->save();

            return ['status'=>200, 'reason'=>'Email verified successfully','user'=>$user];

        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting all genre
     * */
    public static function getAllGenre(){
        try{
            $genres = Genre::select('genres.*')
                ->where('genres.status','active')
                ->get();

            return ['status'=>200, 'data'=>$genres];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting all goal of a client
     * */
    public static function getAllGoal($client_id=''){
        try{
            $goals = Goal::with('steps.attachments','collaborators');
            $goals = $goals->select('goals.*');
            if($client_id != ''){
                $goals = $goals->where('goals.client_id',$client_id);
            }
            $goals = $goals->where('goals.status','active');
            $goals = $goals->get();

            return ['status'=>200, 'data'=>$goals];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Saving goal data
     * */
    public static function storeGoal($request){
        try{
            DB::beginTransaction();

            $goal = NEW Goal();
            $goal->client_id = $request->client_id;
            $goal->goal_type = $request->goal_type;
            $goal->goal_name = $request->goal_name;
            $goal->genre_id = $request->genre_id;
            $goal->priority = $request->priority;
            $goal->completion_percentage = 0;
            $goal->created_at = date('Y-m-d h:i:s');
            $goal->save();

            /*
             * Adding collaborators
             * */
            if(!empty($request->collaborators)){
                $collaborators = explode(',',$request->collaborators);
                foreach($collaborators as $collaborator){
                    $goal_collaborator = NEW GoalCollaborator();
                    $goal_collaborator->goal_id = $goal->id;
                    $goal_collaborator->collaborator_id = $collaborator;
                    $goal_collaborator->created_at = date('Y-m-d h:i:s');
                    $goal_collaborator->save();

                    /*
                     * Creating notification
                     * */
                    $notification_from  = Client::where('id',$request->client_id)->first();
                    $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' is inviting you to collaborate on '.$goal->goal_name;

                    $notification = NEW Notification();
                    $notification->notification_type = 'collaborator_request';
                    $notification->notification_from_id = $request->client_id;
                    $notification->notification_to_id = $collaborator;
                    $notification->goal_id = $goal->id;
                    $notification->link = '';
                    $notification->text = $notification_text;
                    $notification->sent_date = date('Y-m-d h:i:s');
                    $notification->save();
                }
            }

            DB::commit();

            return ['status'=>200, 'id'=>$goal->id];
        }
        catch(\Exception $e){
            DB::rollback();
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting goal details
     * */
    public static function getGoalDetails($goal_id){
        try{
            $goal = Goal::with('steps.attachments','collaborators')->select('goals.*')->where('goals.id',$goal_id)->first();

            return ['status'=>200, 'data'=>$goal];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Updating goal data
     * */
    public static function updateGoal($request)
    {
        try {
            DB::beginTransaction();

            $goal = Goal::where('id', $request->goal_id)->first();
            if (isset($request->client_id)) {
                $goal->client_id = $request->client_id;
            }
            if (isset($request->goal_type)) {
                $goal->goal_type = $request->goal_type;
            }
            if (isset($request->goal_name)) {
                $goal->goal_name = $request->goal_name;
            }
            if (isset($request->genre_id)) {
                $goal->genre_id = $request->genre_id;
            }
            if (isset($request->priority)) {
                $goal->priority = $request->priority;
            }
            $goal->updated_at = date('Y-m-d h:i:s');
            $goal->save();

            /*
             * Adding collaborators
             * */
            if(!empty($request->collaborators)){
                $collaborators = explode(',',$request->collaborators);
                $old_collaborators = GoalCollaborator::select('collaborator_id')
                    ->where('goal_id',$goal->id)
                    ->pluck('collaborator_id')
                    ->toArray();

                // First remove collaborator data
                GoalCollaborator::where('goal_id',$goal->id)->delete();

                // Now re-adding collaborator
                foreach($collaborators as $collaborator){
                    $goal_collaborator = NEW GoalCollaborator();
                    $goal_collaborator->goal_id = $goal->id;
                    $goal_collaborator->collaborator_id = $collaborator;
                    $goal_collaborator->created_at = date('Y-m-d h:i:s');
                    $goal_collaborator->save();

                    /*
                     * Creating notification
                     * */
                    if(!in_array($collaborator,$old_collaborators)){ // If this collaborator not already added
                        $notification_from  = Client::where('id',$request->client_id)->first();
                        $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' is inviting you to collaborate on '.$goal->goal_name;

                        $notification = NEW Notification();
                        $notification->notification_type = 'collaborator_request';
                        $notification->notification_from_id = $request->client_id;
                        $notification->notification_to_id = $collaborator;
                        $notification->goal_id = $request->goal_id;
                        $notification->link = '';
                        $notification->text = $notification_text;
                        $notification->sent_date = date('Y-m-d h:i:s');
                        $notification->save();
                    }
                }
            }

            DB::commit();

            return ['status' => 200, 'id' => $goal->id];
        } catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401, 'reason' => $e->getMessage()];
        }
    }

    /*
     * Deleting goal data
     * */
    public static function deleteGoal($request){
        try{
            $goal = Goal::where('id',$request->goal_id)->first();
            $goal->status = 'deleted';
            $goal->deleted_at = date('Y-m-d h:i:s');
            $goal->save();

            return ['status'=>200, 'id'=>$goal->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Making goal complete
     * */
    public static function makeCompleteGoal($request){
        try{
            $goal = Goal::where('id',$request->goal_id)->first();
            $goal->completion_percentage = 100;
            $goal->completed_at = date('Y-m-d h:i:s');
            $goal->save();

            return ['status'=>200, 'reason'=>'Successfully completed'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }


    /*
     * Saving goal step data
     * */
    public static function storeGoalStep($request){
        try{
            DB::beginTransaction();

            $goal_step = NEW GoalStep();
            $goal_step->goal_id = $request->goal_id;
            $goal_step->step_name = $request->step_name;
            $goal_step->description = $request->description;
            if($request->end_date != ''){
                $goal_step->end_date = date('Y-m-d', strtotime($request->end_date));
            }
            $goal_step->note = $request->note;
            if($request->reminder_time != ''){
                $goal_step->reminder_time = date('Y-m-d h:i:s', strtotime($request->reminder_time));
            }
            $goal_step->step_occurrence = $request->step_occurrence;
            if($request->step_occurrence_weekdays != ''){
                $goal_step->step_occurrence_weekdays = $request->step_occurrence_weekdays;
            }
            $goal_step->created_at = date('Y-m-d h:i:s');
            $goal_step->save();

            /*
             * Uploading and updating clients attachments file
             * */
            if($request->file_data != ''){
                $files = json_decode($request->file_data, true);
                foreach($files as $file){
                    //$file_name = preg_replace('/\s+/', '', $request->step_name)."-".time().".".$request->file_type;
                    $file_name_data = explode('.',$file['name']);
                    $file_name = $file_name_data[0].".".$file['type'];
                    $uri_path = "uploads/goals/" . $file_name;
                    $full_path = public_path() .'/'. $uri_path;
                    $file_data = $file['data'];
                    $file_data = substr($file_data, strpos($file_data, ",")+1);
                    $data = base64_decode($file_data);
                    $success = file_put_contents($full_path, $data);
                    if($success){
                        // Updating file path to step attachment table
                        $step_attachment = NEW GoalStepAttachment();
                        $step_attachment->goal_step_id = $goal_step->id;
                        $step_attachment->file_type = $file['type'];
                        $step_attachment->file = $uri_path;
                        $step_attachment->save();
                    }
                }
            }

            /*
             * Adding collaborators
             * */
            if(!empty($request->collaborators)){
                $collaborators = explode(',',$request->collaborators);
                foreach($collaborators as $collaborator){
                    $goal_collaborator = NEW StepCollaborator();
                    $goal_collaborator->step_id = $goal_step->id;
                    $goal_collaborator->collaborator_id = $collaborator;
                    $goal_collaborator->created_at = date('Y-m-d h:i:s');
                    $goal_collaborator->save();

                    /*
                     * Creating notification
                     * */
                    $notification_from = Goal::select('clients.*')
                        ->join('clients','clients.id','=','goals.client_id')
                        ->where('goals.id',$request->goal_id)
                        ->first();
                    //$notification_from  = Client::where('id',$client_id)->first();
                    $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' is inviting you to collaborate on '.$goal_step->step_name;

                    $notification = NEW Notification();
                    $notification->notification_type = 'step_collaborator_request';
                    $notification->notification_from_id = $notification_from->id;
                    $notification->notification_to_id = $collaborator;
                    $notification->step_id = $goal_step->id;
                    $notification->link = '';
                    $notification->text = $notification_text;
                    $notification->sent_date = date('Y-m-d h:i:s');
                    $notification->save();
                }
            }

            DB::commit();

            // Checking and making goal completed
            Goal::where('id', $goal_step->goal_id)->update([
                'completion_percentage' => self::getCompleteGoalStepPercent($goal_step->goal_id)
            ]);

            return ['status'=>200, 'id'=>$goal_step->id];
        }
        catch(\Exception $e){
            DB::rollback();
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting goal step details
     * */
    public static function getGoalStepDetails($step_id){
        try{
            $goal_step = GoalStep::with('attachments','collaborators')
                ->select('goal_steps.*')
                ->where('goal_steps.id',$step_id)
                ->first();

            return ['status'=>200, 'data'=>$goal_step];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Updating goal step data
     * */
    public static function updateGoalStep($request)
    {
        try {
            DB::beginTransaction();

            $goal_step = GoalStep::where('id', $request->step_id)->first();
            if (isset($request->step_name)) {
                $goal_step->step_name = $request->step_name;
            }
            if (isset($request->description)) {
                $goal_step->description = $request->description;
            }
            if($request->end_date != ''){
                $goal_step->end_date = date('Y-m-d', strtotime($request->end_date));
            }
            if (isset($request->note)) {
                $goal_step->note = $request->note;
            }
            if($request->reminder_time != ''){
                $goal_step->reminder_time = date('Y-m-d h:i:s', strtotime($request->reminder_time));
            }
            if (isset($request->step_occurrence)) {
                $goal_step->step_occurrence = $request->step_occurrence;
            }
            if (isset($request->step_occurrence_weekdays)) {
                $goal_step->step_occurrence_weekdays = $request->step_occurrence_weekdays;
            }
            $goal_step->updated_at = date('Y-m-d h:i:s');
            $goal_step->save();

            /*
             * Uploading and updating clients attachments file
             * */
            if($request->file_data != ''){
                $files = json_decode($request->file_data, true);
                foreach($files as $file){
                    //$file_name = preg_replace('/\s+/', '', $request->step_name)."-".time().".".$request->file_type;
                    $file_name_data = explode('.',$file['name']);
                    $file_name = $file_name_data[0].".".$file['type'];
                    $uri_path = "uploads/goals/" . $file_name;
                    $full_path = public_path() .'/'. $uri_path;
                    $file_data = $file['data'];
                    $file_data = substr($file_data, strpos($file_data, ",")+1);
                    $data = base64_decode($file_data);
                    $success = file_put_contents($full_path, $data);
                    if($success){
                        // Updating file path to step attachment table
                        $step_attachment = NEW GoalStepAttachment();
                        $step_attachment->goal_step_id = $goal_step->id;
                        $step_attachment->file_type = $file['type'];
                        $step_attachment->file = $uri_path;
                        $step_attachment->save();
                    }
                }
            }

            /*
             * Adding collaborators
             * */
            $collaborators = explode(',',$request->collaborators);
            $old_collaborators = StepCollaborator::select('collaborator_id')
                ->where('step_id',$goal_step->id)
                ->pluck('collaborator_id')
                ->toArray();

            // First remove collaborator data
            StepCollaborator::where('step_id',$goal_step->id)->delete();

            // Now re-adding collaborator
            if(!empty($request->collaborators)){
                $collaborators = explode(',',$request->collaborators);
                foreach($collaborators as $collaborator){
                    $goal_collaborator = NEW StepCollaborator();
                    $goal_collaborator->step_id = $goal_step->id;
                    $goal_collaborator->collaborator_id = $collaborator;
                    $goal_collaborator->created_at = date('Y-m-d h:i:s');
                    $goal_collaborator->save();

                    /*
                     * Creating notification
                     * */
                    if(!in_array($collaborator,$old_collaborators)) { // If this collaborator not already added
                        $notification_from = GoalStep::select('clients.*')
                            ->where('goal_steps.id',$request->step_id)
                            ->join('goals','goals.id','=','goal_steps.goal_id')
                            ->join('clients','clients.id','=','goals.client_id')
                            ->first();
                        //$notification_from  = Client::where('id',$client_id)->first();
                        $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' is inviting you to collaborate on '.$goal_step->step_name;

                        $notification = NEW Notification();
                        $notification->notification_type = 'step_collaborator_request';
                        $notification->notification_from_id = $request->client_id;
                        $notification->notification_to_id = $collaborator;
                        $notification->step_id = $goal_step->id;
                        $notification->link = '';
                        $notification->text = $notification_text;
                        $notification->sent_date = date('Y-m-d h:i:s');
                        $notification->save();
                    }
                }
            }

            DB::commit();

            return ['status' => 200, 'id' => $goal_step->id];
        } catch (\Exception $e) {
            DB::rollback();
            return ['status' => 401, 'reason' => $e->getMessage()];
        }
    }

    /*
     * Deleting goal step data
     * */
    public static function deleteGoalStep($request){
        try{
            $goal_step = GoalStep::where('id',$request->step_id)->first();
            $goal_step->status = 'deleted';
            $goal_step->deleted_at = date('Y-m-d h:i:s');
            $goal_step->save();

            return ['status'=>200, 'id'=>$goal_step->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Requesting goal step mark off
     * */
    public static function requestGoalStepMarkOff($request){
        try{
            /*
             * Creating notification
             * */
            $notification_from  = Client::where('id',$request->client_id)->first();
            $goal  = GoalStep::select('goals.id as goal_id','goals.goal_name','goals.client_id','goal_steps.id as step_id','goal_steps.step_name')
                ->where('goal_steps.id',$request->step_id)
                ->join('goals','goals.id','=','goal_steps.goal_id')
                ->first();
            $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' is requesting you to mark off the step '.$goal->step_name.' of goal '.$goal->goal_name;

            $notification = NEW Notification();
            $notification->notification_type = 'goal_step_mark_off_request';
            $notification->notification_from_id = $request->client_id;
            $notification->notification_to_id = $goal->client_id; // Goal creator id
            $notification->goal_id = $goal->goal_id;
            $notification->step_id = $request->step_id;
            $notification->link = '';
            $notification->text = $notification_text;
            $notification->sent_date = date('Y-m-d h:i:s');
            $notification->save();

            return ['status'=>200, 'reason'=>'Goal step mark off request successfully sent'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Making goal step complete
     * */
    public static function makeCompleteGoalStep($request){
        try{
            $goal_step = GoalStep::where('id',$request->step_id)->first();
            $goal_step->is_complete = 1;
            $goal_step->completed_at = date('Y-m-d h:i:s');
            $goal_step->save();

            // Checking and making goal completed
            Goal::where('id', $goal_step->goal_id)->update([
                'completion_percentage' => self::getCompleteGoalStepPercent($goal_step->goal_id)
            ]);

            return ['status'=>200, 'id'=>$goal_step->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Deleting goal step attachment
     * */
    public static function deleteStepAttachment($request){
        try{
            DB::beginTransaction();
            $step_attachment = GoalStepAttachment::where('id',$request->attachment_id)->first();
            GoalStepAttachment::where('id',$request->attachment_id)->delete();

            // Now removing file from directory
            //$file_data = explode('/',$step_attachment->file);
            //$file_name = end($file_data);
            File::delete($step_attachment->file);

            DB::commit();

            return ['status'=>200, 'reason'=>'Successfully deleted'];
        }
        catch(\Exception $e){
            DB::rollback();
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting collaborative step
     * */
    public static function getCollaborativeStep($request){
        try{
            $goal = Goal::where('id', $request->goal_id)->first();
            $collaborative_steps = StepCollaborator::select('goal_steps.id','goal_steps.step_name','goal_steps.end_date','goal_steps.is_complete')
                ->join('goal_steps','goal_steps.id','=','step_collaborators.step_id')
                ->join('goals','goals.id','=','goal_steps.goal_id')
                ->where('collaborator_id',$request->collaborator_id)
                ->where('goals.id',$request->goal_id)
                ->get();
            $goal->steps = $collaborative_steps;

            return ['status'=>200, 'data'=>$goal];
        }
        catch(\Exception $e){
            DB::rollback();
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Making goal step complete
     * */
    private static function getCompleteGoalStepPercent($goal_id){
        try{
            $total_steps = GoalStep::where('goal_id',$goal_id)->where('status','active')->count();
            $completed_steps = GoalStep::where('goal_id',$goal_id)->where('is_complete',1)->where('status','active')->count();

            if($completed_steps==0){
                $percentage = 0;
            }
            else if($completed_steps==$total_steps){
                $percentage = 100;
            }
            else{
                $percentage = ($completed_steps/$total_steps)*100;
            }
            return $percentage;

        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Search goal/step
     * */
    public static function search($request){
        try{
            if($request->search_category=='goal'){
                // Get goals
                $goals= Goal::select('id','goal_name','genre_id','priority');
                $goals = $goals->where('goal_name', 'like', '%' . $request->text . '%');
                //$goals = $goals->where('priority', 2);
                //$goals = $goals->where('genre_id', 1);
                if($request->genre != ''){
                    $goals = $goals->where('genre_id', $request->genre);
                }
                if($request->priority != ''){
                    $goals = $goals->where('priority', $request->priority);
                }
                $goals = $goals->where('client_id', $request->client_id);
                $goals = $goals->where('goals.status', 'active');
                $goals = $goals->get();

                // Get goal steps
                $goal_steps = GoalStep::select('goal_steps.id','goal_steps.step_name','goals.genre_id','goals.priority');
                $goal_steps = $goal_steps->join('goals','goals.id','=','goal_steps.goal_id');
                $goal_steps = $goal_steps->where('step_name', 'like', '%' . $request->text . '%');
                if($request->genre != ''){
                    $goal_steps = $goal_steps->where('goals.genre_id', $request->genre);
                }
                if($request->priority != ''){
                    $goal_steps = $goal_steps->where('goals.priority', $request->priority);
                }
                $goal_steps = $goal_steps->where('goals.client_id', $request->client_id);
                $goal_steps = $goal_steps->where('goal_steps.status', 'active');
                $goal_steps = $goal_steps->get();

                // Get goal assist
                $goal_assists= GoalCollaborator::select('goals.id','goals.goal_name','goals.genre_id','goals.priority');
                $goal_assists = $goal_assists->join('goals','goals.id','=','goal_collaborators.goal_id');
                $goal_assists = $goal_assists->where('goals.goal_name', 'like', '%' . $request->text . '%');
                if($request->genre != ''){
                    $goal_assists = $goal_assists->where('goals.genre_id', $request->genre);
                }
                if($request->priority != ''){
                    $goal_assists = $goal_assists->where('goals.priority', $request->priority);
                }
                $goal_assists = $goal_assists->where('collaborator_id', $request->client_id);
                $goal_assists = $goal_assists->where('goals.status', 'active');
                $goal_assists = $goal_assists->get();

                return ['status'=>200, 'goals'=>$goals, 'goal_steps'=>$goal_steps, 'goal assist'=>$goal_assists];
            }
            else if($request->search_category=='people'){ // If search category is people
                $text = $request->text;
                $clients = Client::select('id','first_name','last_name','email','photo');
                $clients = $clients->where(function($query) use ($text){
                    $query->orwhere('first_name', 'like', '%' . $text . '%');
                    $query->orwhere('last_name', 'like', '%' . $text . '%');
                });
                if($request->email != ''){
                    $clients = $clients->where('email', $request->email);
                }
                $clients = $clients->where('id','!=', $request->client_id); // Ignoring self search
                $clients = $clients->get();

                foreach($clients as $key=>$client){
                    $network = Network::where('client_id',$request->client_id)
                        ->where('connected_with_id',$client->id)
                        ->first();

                    if(!empty($network)){
                        $clients[$key]['connection_status'] = $network->status;
                    }
                    else{
                        $clients[$key]['connection_status'] = 'not connected';
                    }
                }

                return ['status'=>200, 'clients'=>$clients];
            }
            else if($request->search_category=='skill'){ // If search category is skill
                $clients = Client::select('id','first_name','last_name','core_skills','photo')
                    ->where('first_name', 'like', '%' . $request->text . '%')
                    ->orWhere('last_name', 'like', '%' . $request->text . '%')
                    ->orWhere('core_skills', 'like', '%' . $request->text . '%')
                    ->get();

                return ['status'=>200, 'clients'=>$clients];
            }
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Search current goal
     * */
    public static function currentGoalSearch($request){
        try{
            if($request->goal_type=='Active' || $request->goal_type=='Non-Active'){
                $goals= Goal::with(['steps'=>function($query){
                            $query->select('*')->where('is_complete', 0)->limit(1);
                        }])->select('goals.*');
                if($request->text != ''){
                    $goals = $goals->where('goal_name', 'like', '%' . $request->text . '%');
                }
                if($request->goal_type != ''){
                    $goals = $goals->where('goal_type', $request->goal_type);
                }
                if($request->genre != ''){
                    $goals = $goals->where('genre_id', $request->genre);
                }
                if($request->priority != ''){
                    $goals = $goals->where('priority', $request->priority);
                }
                $goals = $goals->where('client_id', $request->client_id);
                $goals = $goals->where('goals.status', 'active');
                $goals = $goals->get();

                return ['status'=>200, 'data'=>$goals];
            }
            else if($request->goal_type=='Goal-Assist'){
                $goals= GoalCollaborator::select('goals.*',DB::raw("CONCAT(clients.first_name,' ',clients.last_name) as owner"));
                $goals = $goals->join('goals','goals.id','=','goal_collaborators.goal_id');
                $goals = $goals->leftJoin('clients','clients.id','=','goals.client_id');
                if($request->text != ''){
                    $goals = $goals->where('goals.goal_name', 'like', '%' . $request->text . '%');
                }
                if($request->genre != ''){
                    $goals = $goals->where('goals.genre_id', $request->genre);
                }
                if($request->priority != ''){
                    $goals = $goals->where('goals.priority', $request->priority);
                }
                $goals = $goals->where('collaborator_id', $request->client_id);
                $goals = $goals->where('goals.status', 'active');
                $goals = $goals->get();

                return ['status'=>200, 'data'=>$goals];
            }
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Adding collaborators to goal
     * */
    public static function addCollaborators($request){
        try{
            $collaborators = explode(',',$request->collaborators);
            foreach($collaborators as $collaborator){
                /*
                 * Check if collaborators already added
                 * */
                $ext_collaborator = GoalCollaborator::where('goal_id',$request->goal_id)
                    ->where('collaborator_id',$collaborator)
                    ->where('status','!=','deleted')
                    ->first();

                if(empty($ext_collaborator)){
                    $goal_collaborator = NEW GoalCollaborator();
                    $goal_collaborator->goal_id = $request->goal_id;
                    $goal_collaborator->collaborator_id = $collaborator;
                    $goal_collaborator->created_at = date('Y-m-d h:i:s');
                    $goal_collaborator->save();

                    /*
                     * Creating notification
                     * */
                    $notification_from  = Client::where('id',$request->client_id)->first();
                    $goal  = Goal::where('id',$request->goal_id)->first();
                    $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' is inviting you to collaborate on '.$goal->goal_name;

                    $notification = NEW Notification();
                    $notification->notification_type = 'collaborator_request';
                    $notification->notification_from_id = $request->client_id;
                    $notification->notification_to_id = $collaborator;
                    $notification->goal_id = $request->goal_id;
                    $notification->link = '';
                    $notification->text = $notification_text;
                    $notification->sent_date = date('Y-m-d h:i:s');
                    $notification->save();
                }
            }
            return ['status'=>200, 'reason'=>'Collaborators added successfully'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting collaborator details
     * */
    public static function getCollaboratorProfile($collaborator_id){
        try{
            $client = Client::select('clients.*')->where('clients.id',$collaborator_id)->first();

            return ['status'=>200, 'data'=>$client];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Removing collaborators to goal
     * */
    public static function deleteCollaborators($request){
        try{
            $goal = Goal::where('id',$request->goal_id)->first();
            GoalCollaborator::where('goal_id',$request->goal_id)
                ->where('collaborator_id',$request->collaborator_id)->delete();

            /*
             * Creating notification
             * */
            $notification_from  = Client::where('id',$goal->client_id)->first();
            $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' has removed you as collaborator from '.$goal->goal_name;

            $notification = NEW Notification();
            $notification->notification_type = 'collaborator_removal';
            $notification->notification_from_id = $goal->client_id;
            $notification->notification_to_id = $request->collaborator_id;
            $notification->goal_id = $request->goal_id;
            $notification->link = '';
            $notification->text = $notification_text;
            $notification->sent_date = date('Y-m-d h:i:s');
            $notification->save();
            return ['status'=>200, 'reason'=>'Collaborators removed successfully'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting client's completed goal details
     * */
    public static function getTrophies($request){
        try{
            //Getting total current goals
            $current_goals = Goal::select('goals.*');
            $current_goals = $current_goals->where('goals.client_id',$request->client_id);
            $current_goals = $current_goals->where('completion_percentage','<',100);
            $current_goals = $current_goals->where('status','!=','deleted');
            $current_goals = $current_goals->count();

            //Getting total completed goals
            $completed_goals = Goal::select('goals.*');
            $completed_goals = $completed_goals->where('goals.client_id',$request->client_id);
            $completed_goals = $completed_goals->where('completion_percentage',100);
            $completed_goals = $completed_goals->where('status','!=','deleted');
            $completed_goals = $completed_goals->get();

            //Getting total deleted goals
            $archieved_goals = Goal::select('goals.*');
            $archieved_goals = $archieved_goals->where('goals.client_id',$request->client_id);
            $archieved_goals = $archieved_goals->where('status','deleted');
            $archieved_goals = $archieved_goals->count();

            $data['total_current_goal'] = $current_goals;
            $data['total_completed_goal'] = count($completed_goals);
            $data['total_archieved_goal'] = $archieved_goals;

            $data['completed_goals'] = $completed_goals;

            return ['status'=>200, 'data'=>$data];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Searching client's completed goal
     * */
    public static function searchCompletedGoal($request){
        try{
            //Getting completed goals
            $goals = Goal::select('goals.*');
            $goals = $goals->where('goals.client_id',$request->client_id);
            $goals = $goals->where('completion_percentage',100);
            $goals = $goals->where('status','!=','deleted');
            $goals = $goals->where('goal_name', 'like', '%' . $request->goal_name . '%');
            $goals = $goals->get();

            return ['status'=>200, 'data'=>$goals];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Get all notifications for this client
     * */
    public static function getNotifications($request){
        try{
            $notifications = Notification::select('notifications.*',DB::raw("CONCAT(notification_from.first_name,' ',notification_from.last_name) as notification_from"), 'goals.goal_name')
                ->leftJoin('clients as notification_from','notification_from.id','=','notifications.notification_from_id')
                ->leftJoin('goals','goals.id','=','notifications.goal_id')
                ->where('notification_to_id',$request->client_id)
                ->orderBy('notifications.id','DESC')
                ->get();

            return ['status'=>200, 'data'=>$notifications];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Get notifications details
     * */
    public static function getNotificationDetails($notification_id=''){
        try{
            $notification = Notification::select('notifications.*',DB::raw("CONCAT(notification_from.first_name,' ',notification_from.last_name) as notification_from"), 'goals.goal_name')
                ->leftJoin('clients as notification_from','notification_from.id','=','notifications.notification_from_id')
                ->leftJoin('goals','goals.id','=','notifications.goal_id')
                ->where('notifications.id',$notification_id)
                ->first();

            return ['status'=>200, 'data'=>$notification];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Update notifications details
     * */
    public static function updateNotification($request){
        try{
            $notification = Notification::where('notifications.id',$request->notification_id)->first();
            if($request->action != ''){
                $notification->action = $request->action;
            }
            $notification->updated_at = date('Y-m-d h:i:s');
            $notification->save();

            return ['status'=>200, 'reason'=>'Successfully updated'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting my network connection
     * */
    public static function getMyNetworkConnection($request){
        try{
            $networks_to = Network::select('networks.id','clients.id as connected_with_id',DB::raw("CONCAT(clients.first_name,' ',clients.last_name) as connected_with_name"),'networks.status','users.phone','users.email','users.photo')
                ->leftJoin('clients','clients.id','=','networks.connected_with_id')
                ->leftJoin('users','users.id','=','clients.user_id')
                ->where('networks.client_id',$request->client_id)
                ->get()->toArray();

            $networks_from = Network::select('networks.id','clients.id as connected_with_id',DB::raw("CONCAT(clients.first_name,' ',clients.last_name) as connected_with_name"),'networks.status','users.phone','users.email','users.photo')
                ->leftJoin('clients','clients.id','=','networks.client_id')
                ->leftJoin('users','users.id','=','clients.user_id')
                ->where('networks.connected_with_id',$request->client_id)
                ->get()->toArray();

            //$networks = array_merge($networks_to,$networks_from);
            $networks = array_merge($networks_to, $networks_from);
            return ['status'=>200, 'data'=>$networks];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * add network connection
     * */
    public static function addNetworkConnection($request){
        try{
            $existing_network = Network::where('client_id',$request->client_id)
                ->where('connected_with_id',$request->connected_with_id)
                ->first();
            if(!empty($existing_network)){
                return ['status'=>401, 'reason'=>'This person already connected with you'];
            }

            $network = NEW Network();
            $network->client_id = $request->client_id;
            $network->connected_with_id = $request->connected_with_id;
            $network->request_sent_date = date('Y-m-d h:i:s');
            $network->save();


            /*
             * Creating notification
             * */
            $notification_from  = Client::where('id',$request->client_id)->first();
            $notification_text = $notification_from->first_name.' '.$notification_from->last_name.' wants to connect with you';

            $notification = NEW Notification();
            $notification->notification_type = 'network_connection_request';
            $notification->notification_from_id = $request->client_id;
            $notification->notification_to_id = $request->connected_with_id;
            $notification->network_id = $network->id;
            $notification->link = '';
            $notification->text = $notification_text;
            $notification->sent_date = date('Y-m-d h:i:s');
            $notification->save();

            return ['status'=>200, 'reason'=>'Connection request successfully sent'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting my network connection
     * */
    public static function viewNetworkConnection($network_id){
        try{
            $networks = Network::select('networks.id','cw.id as connected_with_id',DB::raw("CONCAT(c.first_name,' ',c.last_name) as connected_by_name"),DB::raw("CONCAT(cw.first_name,' ',cw.last_name) as connected_with_name"),'networks.status','users.phone','users.email','users.photo')
                ->leftJoin('clients as c','c.id','=','networks.client_id')
                ->leftJoin('clients as cw','cw.id','=','networks.connected_with_id')
                ->leftJoin('users','users.id','=','cw.user_id')
                ->where('networks.id',$network_id)
                ->first();
            return ['status'=>200, 'data'=>$networks];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Accept network connection request
     * */
    public static function acceptNetworkConnection($network_id){
        try{
            $networks = Network::where('networks.id',$network_id)->first();
            $networks->status = 'accepted';
            $networks->accept_date = date('Y-m-d h:i:s');
            $networks->save();
            return ['status'=>200, 'reason'=>'Connection accepted successfully'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Decline network connection request
     * */
    public static function declineNetworkConnection($request){
        try{
            // Remove from network table
            Network::where('networks.id',$request->network_id)->delete();

            // Now remove from notification table
            Notification::where('id',$request->notification_id)->delete();

            return ['status'=>200, 'reason'=>'Connection declined'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Remove network connection
     * */
    public static function removeNetworkConnection($request){
        try{
            // Remove from network table
            Network::where('networks.id',$request->network_id)->delete();

            return ['status'=>200, 'reason'=>'Connection removed'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting support tickets
     * */
    public static function getTickets($request){
        try{
            $tickets = SupportTicket::where('sender_id',$request->client_id)
                ->whereIn('status',['active','closed'])
                ->orderBy('id','desc')
                ->get();

            return ['status'=>200, 'data'=>$tickets];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting support ticket details
     * */
    public static function getTicketDetails($request){
        try{
            $ticket = SupportTicket::with('replies')
                ->where('id',$request->ticket_id)
                ->first();

            return ['status'=>200, 'data'=>$ticket];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Saving support ticket
     * */
    public static function storeSupportTicket($request){
        try{
            $ticket = NEW SupportTicket();
            $ticket->sender_id = $request->client_id;
            $ticket->name = $request->name;
            $ticket->email = $request->email;
            $ticket->message = $request->message;
            $ticket->save();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Saving support ticket reply
     * */
    public static function storeSupportTicketReply($request){
        try{
            $reply = NEW SupportTicketReply();
            $reply->ticket_id = $request->ticket_id;
            $reply->sender_type = 'client';
            $reply->receiver_type = 'admin';
            $reply->name = $request->name;
            $reply->email = $request->email;
            $reply->message = $request->message;
            $reply->save();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }


}//End
