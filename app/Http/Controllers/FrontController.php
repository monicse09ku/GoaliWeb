<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use App\Common;
use Auth;
use Session;
use DB;

class FrontController extends Controller
{
    public function __construct()
    {

    }

    public function terms_condition(Request $request)
    {
        try{
            $page_data = Page::where('page_name','terms_condition')->first();
            return view('terms_condition',compact('page_data'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }
}
