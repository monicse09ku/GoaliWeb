<?php

namespace App;
use App\Models\Client;
use App\Models\Genre;
use App\Models\Goal;
use App\Models\GoalStep;
use App\Models\GoalCollaborator;
use App\Models\Notification;
use App\Models\Network;
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

            $user = User::select('users.id','users.name','users.email','users.phone','users.photo','users.role','users.oauth_token','clients.id as client_id')
                ->join('clients','clients.user_id','=','users.id')
                ->where('users.id',$user->id)
                ->first();

            Db::commit();

            return ['status'=>200, 'token'=>$token, 'user'=>$user];
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
            $goals = Goal::with('steps','collaborators');
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
                    $notification->goal_id = $request->goal_id;
                    $notification->link = '';
                    $notification->text = $notification_text;
                    $notification->sent_date = date('Y-m-d h:i:s');
                    $notification->save();
                }
                /*
                 * Creating notification
                 * */
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
            $goal = Goal::with('steps')->select('goals.*')->where('goals.id',$goal_id)->first();

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
     * Getting goal step details
     * */
    public static function getGoalStepDetails($step_id){
        try{
            $goal_step = GoalStep::select('goal_steps.*')->where('goal_steps.id',$step_id)->first();

            $collaborators = GoalCollaborator::select('clients.id as collaborator_id',DB::raw("CONCAT(clients.first_name,' ',clients.last_name) as collaborator_name"));
            $collaborators = $collaborators->leftJoin('clients','clients.id','=','goal_collaborators.collaborator_id');
            $collaborators = $collaborators->where('goal_id', $goal_step->goal_id);
            $collaborators = $collaborators->get();

            $goal_step['collaborators'] = $collaborators;

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
                $goals = $goals->get();

                // Get goal steps
                $goal_steps = GoalStep::select('goal_steps.id','goal_steps.step_name','goals.genre_id','goals.priority');
                $goal_steps = $goal_steps->join('goals','goals.id','=','goal_steps.goal_id');
                $goal_steps = $goal_steps->where('step_name', 'like', '%' . $request->text . '%');
                if($request->genre != ''){
                    $goals = $goals->where('goals.genre_id', $request->genre);
                }
                if($request->priority != ''){
                    $goals = $goals->where('goals.priority', $request->priority);
                }
                $goal_steps = $goal_steps->where('goals.client_id', $request->client_id);
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
     * Getting my network connection
     * */
    public static function getMyNetworkConnection($request){
        try{
            $networks = Network::select('networks.id','clients.id as connected_with_id',DB::raw("CONCAT(clients.first_name,' ',clients.last_name) as connected_with_name"),'networks.status','users.phone','users.email','users.photo')
                ->leftJoin('clients','clients.id','=','networks.connected_with_id')
                ->leftJoin('users','users.id','=','clients.user_id')
                ->where('networks.client_id',$request->client_id)
                ->get();
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


}//End
