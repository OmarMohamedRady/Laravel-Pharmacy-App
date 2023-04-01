<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        $clients=Client::all();
        return view('clients.index',['clients'=>$clients]);
    }

    public function show( $id)
    {
        // @dd($client);
       $client = Client::find($id);



        return view('clients.show', [
            "client" => $client
        ]);
    }

    public function create(Request $request){

        return view('clients.create');

    }

    

    public function store(Request $request){

       
            $title = request()->title;
            $description = request()->description;
            $clientCreator = request()->client_creator;
    
            
            $client = Client::create([
                'name' =>  $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'national_id'=>$request->national_id,
                'birth_day'=>$request->birth_day,
                'mobile'=>$request->mobile,

            ]);
    
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $filename = $avatar->getClientOriginalName();
                $path = $request->file('avatar')->storeAs('clientImgs', $filename, 'public');
                $client->avatar_path= $path;
                $client->save();
            }
    
            return redirect()->route('clients.index');
            
    }

    public function edit($client){
        $client = Client::all();
        // $post = Post::find($post);
        
        return view('clients.edit', ['client' => $client]);
    }

    public function destroy($client){
        
        $client = Client::findOrFail($client);
        // if ($post->image_path && Storage::exists('public/'.$post->image_path)) {
        //     Storage::delete('public/'.$post->image_path);
        // }
        $client->delete();
    
        return redirect()->route('clients.index');
        
    }
   

}