<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Common;
use Auth;
use Session;
use File;
use DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request){
        try {
            if (Auth::check()) {
                if(Session::get('role') == 1 || Session::get('role') == 2) { // If user role is super admin or admin
                    $user = Auth::user();
                    $roles = [
                        '2' => 'Admin'
                    ];
                    $users = User::select('users.*');
                    $users = $users->where('users.id', '!=', Session::get('user_id'));
                    $users = $users->whereIn('users.status', ['active', 'inactive']);
                    $users = $users->whereIn('users.role', [2]);
                    $users = $users->orderBy('users.id', 'ASC');
                    $users = $users->get();

                    return view('user.index', compact('roles', 'users'));
                }
                else{
                    return redirect('error_404');
                }
            }
            else{
                return redirect('login');
            }
        }
        catch (\Exception $e) {
            //SendMails::sendErrorMail($e->getMessage(), null, 'UserController', 'userList', $e->getLine(),
            //$e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return [ 'status' => 401, 'reason' => 'Something went wrong. Try again later'];
        }
    }

    public function create(Request $request)
    {
        if (Auth::check()) {
            if(Session::get('role') == 1 || Session::get('role') == 2) { // If user role is super admin or admin
                $admins = User::select('users.*');
                $admins = $admins->where('users.id','!=',Session::get('user_id'));
                $admins = $admins->where('users.status','active');
                $admins = $admins->where('users.role',2);
                $admins = $admins->orderBy('users.name', 'ASC');
                $admins = $admins->get();

                $users = User::select('users.*');
                $users = $users->where('users.id','!=',Session::get('user_id'));
                $users = $users->where('users.status','active');
                $users = $users->where('users.role',3);
                $users = $users->orderBy('users.name', 'ASC');
                $users = $users->get();

                if($request->ajax()) {
                    $returnHTML = View::make('user.create_new_user',compact('admins','users'))->renderSections()['content'];
                    return response()->json(array('status' => 200, 'html' => $returnHTML));
                }
                return view('user.create',compact('admins','users'));
            }
            else{
                return redirect('error_404');
            }
        }
        else{
            return redirect('login');
        }
    }

    public function store(Request $request){
        try {
            DB::beginTransaction();

            /*
             * checking for duplicate email
             * */
            $user_exist = User::where('email',$request->email)->first();
            if(!empty($user_exist)){
                return ['status'=>401, 'reason'=>'This email address already exists. Try again with another email address.'];
            }

            $password = $request->password;

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->username = $request->username;
            $user->password = bcrypt($password);
            $user->phone = $request->phone;
            $user->role = $request->role;
            $user->status = 'active';

            /*
             * Update profile photo
             * */
            $photo_path = '';
            if($request->hasFile('photo')){
                $file = $request->File('photo');
                $extension = $file->getClientOriginalExtension();
                $file_name = md5(rand(10,10000)).time().'.'.$file->getClientOriginalExtension();
                $destinationPath = public_path('uploads/users');
                if(!File::exists($destinationPath)){
                    File::makeDirectory($destinationPath, 0775, true);
                }
                $file->move($destinationPath, $file_name);

                $photo_path = 'uploads/users/'.$file_name;
                $user->photo = $photo_path;
            }

            $user->save();

            DB::commit();

            return ['status' => 200, 'reason' => 'New user created successfully','user_id'=>$user->id];
        } catch (\Exception $e) {
            //SendMails::sendErrorMail($e->getMessage(), null, 'UserController', 'storeNewUser', $e->getLine(),
            //$e->getFile(), '', '', '', '');
            // message, view file, controller, method name, Line number, file,  object, type, argument, email.
            return [ 'status' => 401, 'reason' => 'Something went wrong. Try again later'];
        }
    }

    public function edit(Request $request)
    {
        if (Auth::check()) {
            if(Session::get('role') == 1 || Session::get('role') == 2) { // If user role is super admin or admin
                $users = User::select('users.*');
                $users = $users->where('users.id','!=',Session::get('user_id'));
                $users = $users->where('users.status','active');
                $users = $users->where('users.role',3);
                $users = $users->orderBy('users.name', 'ASC');
                $users = $users->get();

                $user = User::where('users.id', $request->id)
                    ->select('users.*')
                    ->first();

                return view('user.edit', compact('users','user'));
            }
            else{
                return redirect('error_404');
            }
        }
        else{
            return redirect('login');
        }
    }

    public function update(Request $request){
        try{
            $password = $request->password;

            /*
             * checking for duplicate email
             * */
            $user_exist = User::where('email',$request->email)->first();
            if(!empty($user_exist) && $user_exist->id != $request->user_id){
                return ['status'=>401, 'reason'=>'This email address already exists. Try again with another email address.'];
            }

            $user = User::where('id',$request->user_id)->first();
            $user->name = $request->name;
            $user->phone = $request->phone;

            if($password != ''){
                $user->password = bcrypt($password);
            }
            if($request->role != ''){
                $user->role = $request->role;
            }

            /*
             * Update profile photo
             * */
            $photo_path = '';
            $old_photo_path = $request->old_photo_path;
            if($request->hasFile('photo')){
                $file = $request->File('photo');
                $extension = $file->getClientOriginalExtension();
                $file_name = md5(rand(10,10000)).time().'.'.$file->getClientOriginalExtension();
                $destinationPath = public_path('uploads/users');
                if(!File::exists($destinationPath)){
                    File::makeDirectory($destinationPath, 0775, true);
                }
                $file->move($destinationPath, $file_name);

                $photo_path = 'uploads/users/'.$file_name;
                $user->photo = $photo_path;

                // Delete previous photo from physical folder
                if (file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }

            $user->updated_at = date('Y-m-d h:i:s');
            $user->save();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again'];
        }
    }

    public function profile(){
        try{
            $user = Auth::user();

            if($user->role==4){
                return view('customer_profile',compact('user'));
            }
            else{
                return view('profile',compact('user'));
            }

        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function resetPassword(){
        try{
            $user = Auth::user();

            return view('reset_password',compact('user'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function updatePassword(Request $request){
        try{
            $currentUser = Auth::user();
            /*
             * Check if valid user requesting for password update
             * */
            $result = Auth::attempt([
                'email' => $currentUser->email,
                'password' => $request->current_password
            ]);

            if (!$result) {
                return ['status'=>401, 'reason'=>'Unauthorised user. You have no rights to update password'];
            }
            $user = User::where('id',$request->user_id)->first();
            $user->password = bcrypt($request->password);
            $user->updated_at = date('Y-m-d h:i:s');
            $user->save();

            return ['status'=>200, 'reason'=>'Successfully updated'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again'];
        }
    }

    public function delete(Request $request){
        try{
            $user = User::where('id',$request->user_id)->first();
            $user->status = 'deleted';
            $user->deleted_at = date('Y-m-d h:i:s');
            $user->save();

            return ['status'=>200, 'reason'=>'Successfully deleted'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again'];
        }
    }
}
