<?php

namespace App;
use App\Models\Client;
use App\Models\Genre;
use App\Models\Goal;
use App\Models\GoalStep;
use App\Models\GoalCollaborator;
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

    public static function hasAccess($token){
        $user = User::where('oauth_token',$token)->first();
        if(!empty($user)){
            return 1;
        }
        else{
            return 0;
        }
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
     * Saving client data
     * */
    public static function storeClient($request){
        try{
            DB::beginTransaction();

            $old_client = User::where('username',$request->username)->where('status','!=','deleted')->first();
            if(!empty($old_client)){
                return ['status'=>401, 'reason'=>'This client username already exists'];
            }

            $token = Common::generaterandomString(8);

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
            $user->status = 'active';
            $user->save();

            /*
             * Update client user id
             * */
            $client_update = Client::where('id',$client->id)->first();
            $client_update->user_id = $user->id;
            $client_update->save();

            Db::commit();

            return ['status'=>200, 'id'=>$client->id, 'token'=>$token];
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
            $goals = Goal::select('goals.*');
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
            $goal = NEW Goal();
            $goal->client_id = $request->client_id;
            $goal->goal_type = $request->goal_type;
            $goal->goal_name = $request->goal_name;
            $goal->genre_id = $request->genre_id;
            $goal->priority = $request->priority;
            $goal->completion_percentage = 0;
            $goal->created_at = date('Y-m-d h:i:s');
            $goal->save();

            return ['status'=>200, 'id'=>$goal->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting goal details
     * */
    public static function getGoalDetails($goal_id){
        try{
            $goal = Goal::select('goals.*')->where('goals.id',$goal_id)->first();

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

            return ['status' => 200, 'id' => $goal->id];
        } catch (\Exception $e) {
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
     * Saving goal step data
     * */
    public static function storeGoalStep($request){
        try{
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

            return ['status'=>200, 'id'=>$goal_step->id];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }

    /*
     * Getting goal step details
     * */
    public static function getGoalStepDetails($step_id){
        try{
            $goal_step = GoalStep::select('goal_steps.*')->where('goal_steps.id',$step_id)->first();

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

            return ['status' => 200, 'id' => $goal_step->id];
        } catch (\Exception $e) {
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
     * Making goal step complete
     * */
    public static function makeCompleteGoalStep($request){
        try{
            $goal_step = GoalStep::where('id',$request->step_id)->first();
            $goal_step->is_complete = 1;
            $goal_step->completed_at = date('Y-m-d h:i:s');
            $goal_step->save();

            return ['status'=>200, 'id'=>$goal_step->id];
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
                $goals= Goal::select('id','goal_name')
                    ->where('goal_name', 'like', '%' . $request->text . '%')
                    ->where('client_id', $request->client_id)
                    ->get();

                $goal_steps = GoalStep::select('goal_steps.id','goal_steps.step_name')
                    ->join('goals','goals.id','=','goal_steps.goal_id')
                    ->where('step_name', 'like', '%' . $request->text . '%')
                    ->where('goals.client_id', $request->client_id)
                    ->get();

                return ['status'=>200, 'goals'=>$goals, 'goal_steps'=>$goal_steps];
            }
            else if($request->search_category=='people'){ // If search category is people
                $clients = Client::select('id','first_name','last_name','photo')
                    ->where('first_name', 'like', '%' . $request->text . '%')
                    ->orWhere('last_name', 'like', '%' . $request->text . '%')
                    ->get();

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
                }
            }
            return ['status'=>200, 'reason'=>'Collaborators added successfully'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>$e->getMessage()];
        }
    }


}//End
