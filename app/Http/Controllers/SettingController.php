<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Mechanic;
use App\Common;
use Auth;
use Session;

class SettingController extends Controller
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
    public function generalSetting(Request $request)
    {
        try{
            $general_settings = Setting::first();
            return view('general_settings',compact('general_settings'));

        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function generalSettingUpdate(Request $request)
    {
        try{
            $general_settings = Setting::first();
            if(empty($general_settings)){
                $general_settings = NEW Setting();
            }
            $general_settings->company_name = $request->company_name;
            $general_settings->phone_number = $request->phone_number;
            $general_settings->company_email = $request->company_email;
            $general_settings->updated_at = date('Y-m-d h:i:s');
            $general_settings->save();
            return ['status'=>200, 'reason'=>'Successfully updated'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Please try again later.'];
        }
    }

    public function mechanic(Request $request)
    {
        try{
            $mechanics = Mechanic::select('mechanics.*');
            $mechanics = $mechanics->where('mechanics.status','active');
            $mechanics = $mechanics->orderBy('mechanics.id','ASc');
            $mechanics = $mechanics->paginate(100);
            return view('mechanic.index',compact('mechanics'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function mechanicCreate(Request $request)
    {
        try{
            return view('mechanic.create');
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function mechanicStore(Request $request)
    {
        try{
            $user = Auth::user();

            $old_mechanic = Mechanic::where('name',$request->name)->where('status','!=','deleted')->first();
            if(!empty($old_mechanic)){
                return ['status'=>401, 'reason'=>'mechanic with this name already exists'];
            }

            $mechanic = NEW Mechanic();
            $mechanic->name = $request->name;
            $mechanic->created_by = $user->id;
            $mechanic->created_at = date('Y-m-d h:i:s');
            $mechanic->save();


            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }

    public function mechanicEdit(Request $request)
    {
        try{
            $mechanic = Mechanic::select('mechanics.*')
                ->where('mechanics.id',$request->id)
                ->first();
            return view('mechanic.edit',compact('mechanic'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function mechanicUpdate(Request $request)
    {
        try{
            $user = Auth::user();

            $old_mechanic = Mechanic::where('name',$request->name)->where('status','!=','deleted')->first();
            if(!empty($old_mechanic) && $old_mechanic->id != $request->id){
                return ['status'=>401, 'reason'=>'mechanic with this name already exists'];
            }

            $mechanic = Mechanic::where('id',$request->id)->first();
            $mechanic->name = $request->name;
            $mechanic->updated_by = $user->id;
            $mechanic->updated_at = date('Y-m-d h:i:s');
            $mechanic->save();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }

    public function mechanicDelete(Request $request)
    {
        try{
            $user = Auth::user();

            $mechanic = Mechanic::where('id',$request->mechanic_id)->first();
            $mechanic->status = 'deleted';
            $mechanic->deleted_by = $user->id;
            $mechanic->deleted_at = date('Y-m-d h:i:s');
            $mechanic->save();

            return ['status'=>200, 'reason'=>'Successfully deleted'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }
}
