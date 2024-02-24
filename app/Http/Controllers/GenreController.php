<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Genre;
use App\Common;
use Auth;
use File;
use Session;
use DB;

class GenreController extends Controller
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
    public function index(Request $request)
    {
        try{
            $genres = Genre::select('genres.*');
            $genres = $genres->whereIn('genres.status',['active','inactive']);
            $genres = $genres->paginate(50);
            return view('genre.index',compact('genres'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function create(Request $request)
    {
        try{
            return view('genre.create');
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function store(Request $request)
    {
        try{
            $old_genre = Genre::where('name',$request->name)->where('status','!=','deleted')->first();
            if(!empty($old_genre)){
                return ['status'=>401, 'reason'=>'This genre name already exists'];
            }

            $genre = NEW Genre();
            $genre->name = $request->name;
            $genre->created_at = date('Y-m-d h:i:s');
            $genre->save();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            DB::rollback();
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }

    public function edit(Request $request)
    {
        try{
            $genre = Genre::select('genres.*')
                ->where('genres.id',$request->id)
                ->first();
            return view('genre.edit',compact('genre'));
        }
        catch(\Exception $e){
            return redirect('error_404');
        }
    }

    public function update(Request $request)
    {
        try{
            $genre = Genre::where('id',$request->id)->first();
            $genre->name = $request->name;
            $genre->updated_at = date('Y-m-d h:i:s');
            $genre->save();

            return ['status'=>200, 'reason'=>'Successfully saved'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }

    public function delete(Request $request)
    {
        try{
            $genre = Genre::where('id',$request->genre_id)->first();
            $genre->status = 'deleted';
            $genre->save();
            return ['status'=>200, 'reason'=>'Successfully deleted'];
        }
        catch(\Exception $e){
            return ['status'=>401, 'reason'=>'Something went wrong. Try again later.'];
        }
    }
}
