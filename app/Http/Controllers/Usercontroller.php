<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class Usercontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input();               
        
        try {
            $query = User::query();            
            
            if (!empty($filters)){
                foreach ($filters as $key => $value){                                          
                    $query->where($key, '=', $value);
                }
            }                               

            $query->select(
                'users.*'
            );
            
            $query->orderBy('created_at', 'DESC');
            
            return response()->json([
                    "data" => $query->get(),  
                    'type' => 'success'
                ], 200);            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . " " . $e->getLine() . " " . $e->getFile(),
                'message' => trans('msgs.msg_error_model_no_exist', ['model' => 'Category']),
                'type' => 'error'
            ], 422);
        }                                                                  
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required|max:10',
            'last_name'=>'required|max:10',
            'age'=>'required',
            'sex'=>'required',
            'email'=>'required',
        ]);                
        
        if($validator->fails()){
            return response()->json([
                'message' => $validator->messages(),
                'type' => 'error'
            ], 400);
        }

        $getData = User::where('email', $request->email)->get();
        
        if (count($getData)>0){
            return response()->json([
                'message' => 'The user already exist',
                'type' => 'error'
            ], 400);            
        }
        
        try {
            $model = User::create(
            [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'age' => $request->age,
                'sex' => $request->sex,
                'email' => $request->email,
            ]);         
            
            return response()->json([
                    "data" => $model, 
                    'type' => 'success'
                ], 200);            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . " " . $e->getLine() . " " . $e->getFile(),
                'message' => trans('msgs.msg_error_model_no_exist', ['model' => 'Category']),
                'type' => 'error'
            ], 422);
        }                
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $model = User::find($id);  
            
            return response()->json([
                    "data" => $model, 
                    'type' =>'success'
                ], 200);                        
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . " " . $e->getLine() . " " . $e->getFile(),
                'type' => 'error'
            ], 422);
        }               
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $model = User::findOrFail($request->id);
            
            $input = $request->all();
            
            $model->update($input);
                    
            return response()->json([
                    "data" => $model, 
                    'type' => trans('msgs.type_success')
                ], 200);            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . " " . $e->getLine() . " " . $e->getFile(),
                'message' => trans('msgs.msg_error_model_no_exist', ['model' => 'Category']),
                'type' => trans('msgs.type_error')
            ], 422);
        }                
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $model = User::where('id','=', $request->id)->delete();
            
            return response()->json([
                    "data" => $model, 
                    'type' => 'success'
                ], 200);            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . " " . $e->getLine() . " " . $e->getFile(),
                'type' => 'error'
            ], 422);
        }                
    }
    
    public function getToken(){
        
        $data = DB::table('oauth_clients')->get();
        
        if (count($data) > 0){
        
            $response = Http::asForm()->post('http://localhost:8080/test-ANF/public/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $data[0]->id,
                'client_secret' => $data[0]->secret,
            ]);
        
            return $response->json()['access_token'];        
        }

        return 'false';        
    }
}
