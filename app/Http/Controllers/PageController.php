<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Common;
use Auth;
use Session;

class PageController extends Controller
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
    public function termsCondition(Request $request)
    {
        try{
            $page_data = Page::where('page_name','terms_condition')->first();
            return view('pages.terms_condition',compact('page_data'));

        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function updatePageData(Request $request)
    {
        try{
            echo "<pre>"; print_r($request->all()); echo "</pre>"; exit();
            $page = Page::where('page_name',$request->page_name)->first();
            if(empty($page)){
                $page = NEW Page();
                $page->page_name = 'terms_condition';
            }
            $page->page_content = trim($request->page_content);
            $page->updated_at = date('Y-m-d h:i:s');
            $page->save();
            return ['status'=>200, 'reason'=>'Successfully updated'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Please try again later.'];
        }
    }
}
