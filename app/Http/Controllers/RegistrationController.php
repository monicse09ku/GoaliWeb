<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerVehicleCredential;
use App\Models\User;
use Illuminate\Http\Request;
use App\Common;
use Auth;
use Session;
use DB;

class RegistrationController extends Controller
{
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        try{
            return view('registration');
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function store(Request $request)
    {
        //try{

            DB::beginTransaction();

            $old_customer = User::where('email',$request->email)->where('status','!=','deleted')->first();
            if(!empty($old_customer)){
                return ['status'=>401, 'reason'=>'This customer email already exists'];
            }

            $customer = NEW Customer();
            $customer->first_name = $request->first_name;
            $customer->last_name = $request->last_name;
            $customer->address = $request->address;
            $customer->phone = $request->phone;
            $customer->email = $request->email;
            $customer->password = bcrypt($request->password);
            $customer->created_at = date('Y-m-d h:i:s');
            $customer->save();

            /*
             * Adding user information
             * */
            $user = new User();
            $user->name = $request->first_name." ".$request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->phone = $request->phone;
            $user->role = 4;
            $user->status = 'active';
            $user->save();

            /*
             * Update GAS customer number and user id
             * */
            $customer_update = Customer::where('id',$customer->id)->first();
            $customer_update->registration_number = 'G'.Common::addLeadingZero($customer->id,5);
            $customer_update->user_id = $user->id;
            $customer_update->save();

            Db::commit();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        /*}
        catch(\Exception $e){
            DB::rollback();
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }*/
    }
}
