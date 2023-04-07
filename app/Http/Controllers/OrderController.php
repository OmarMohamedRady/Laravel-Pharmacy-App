<?php

namespace App\Http\Controllers;
use App\DataTables\OrdersDataTable;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Address;
use App\Models\Client;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\MedicinesOrder;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\PharmaciesMedicines;
use App\Notifications\NotifyUserOrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
class OrderController extends Controller
{
       /* sign_in by admin or doctor or user pass it as argus in link.
        check using has role method

        if admin get all orders table records + order_create_by_column.

        if doctor  get all orders belongs to this pharmacy 

        if user get all orders belongs to this user only

      
        return view('orders.index');*/
        public function index(OrdersDataTable $dataTable)
    {
        
            return $dataTable->render('orders.index');
        

    }

    public function show($id)
    {
        // $user =auth()->user();
        // dd($user);
        $order = Order::where('id', $id)->first();
        $medicine_order = MedicinesOrder::where('order_id',$id)->get();
        // dd($medicine_order[0]['quantity']);
       
        // $user->notify(new NotifyUserOrderDetails($order));
        // dd("done");
        
        return view('orders.show' ,['order' => $order,'medicine_order'=>$medicine_order]);
    }

    public function create()
    {
        $allClients = Client::all();
        $allMedicines = Medicine::all();
        $allAddresses = Address::all();
        $allPharmacies = Pharmacy::all();
        $allDoctors = Doctor::all();
       return view('orders.create',['clients'=>$allClients,'medicines' => $allMedicines,'addresses'=>$allAddresses ,'pharmacies'=>$allPharmacies , 'doctors' =>$allDoctors ]);
    }

    public function store(StoreOrderRequest $request)
    {
        if($request->newMedicine!=null){
            Medicine::create([
            'name' => $request->name,
            'price' => $request->price,   
			'type' => $request->type,
            ]);
            
            PharmaciesMedicines::create([
                      
                'medicine_id' =>$medicine['id'],
                'pharmacy_id' =>$request->pharmacy_id,
                'quantity' =>$request->quantity,
            ]);

        }

                
        
        $orderTotalPrice=0;
        $medTotalPrices =request()->total_price;
        foreach($medTotalPrices as $medTotalPrice)
        {
            $orderTotalPrice+=intval($medTotalPrice);
        }
        //error if not filled
            $client=json_decode(request()->client_name, true);
            
            $client_id =json_decode(request()->client_name, true)['id']; 
            $medicines =request()->medicine_name;
            
           
            // dd($medicines);
            $medicine_quantity =request()->medicine_qty;
            $is_insured =request()->is_insured;
            $doctor_id = request()->doctor_name!=NULL?request()->doctor_name:"";
            $pharmacy_id= request()->pharmacy_name!=NULL?request()->pharmacy_name:"";
            $address_id=request()->delivering_address;
        if(auth()->user()->hasRole('admin')){
        $order=Order::create([
            'is_insured'=>$is_insured,
            'total_price'=>$orderTotalPrice,
            'client_id'=>$client_id,
            'pharmacy_id'=>$pharmacy_id,
            'doctor_id'=>null,
            'address_id'=>$address_id,
            'status'=>1,
            'creator_type'=>'pharmacy',
        ]);

        
    }elseif(auth()->user()->hasRole('pharmacy')){
        $order=Order::create([
            'is_insured'=>$is_insured,
            'total_price'=>$orderTotalPrice,
            'client_id'=>$client_id,
            'pharmacy_id'=>auth()->user()->typeable_id,
            'doctor_id'=>null,
            'address_id'=>$address_id,
            'status'=>1,
            'creator_type'=>'pharmacy',
        ]);
    }else{
        $order=Order::create([
            'is_insured'=>$is_insured,
            'total_price'=>$orderTotalPrice,
            'client_id'=>$client_id,
            'pharmacy_id'=>auth()->user()->typeable->pharmacy_id,
            'doctor_id'=>auth()->user()->typeable_id,
            'address_id'=>$address_id,
            'status'=>2,
            'creator_type'=>'doctor',
        ]);
    }
   
        for($i = 0 ; $i<count($medicines);$i++){
            $medicine= json_decode($medicines[$i], true);
            $medicine_order=MedicinesOrder::create([
               
                
                'order_id' =>$order['id'],
                'medicine_id' =>$medicine['medicine_id'],
                'quantity' =>$medicine_quantity[$i],
        
            ]);
            
         }
         
         $client = User::where('typeable_id', $client_id)->where('typeable_type',"App\Models\Client")->first();
        //  dd($client);
        
         Notification::send($client,new NotifyUserOrderDetails($order,$medicines));
        // dd($medicines);
        return to_route('orders.index');
    }

    public function edit($id)
   
    {
      
        $order= Order::find($id);
        $allClients = Client::all();
        
        $allMedicines = PharmaciesMedicines::all();
        $allAddresses = Address::all();
        $allPharmacies = Pharmacy::all();
        $allDoctors = Doctor::all();
        
        return view('orders.edit', ['order'=>$order, 'clients'=>$allClients,'medicines' => $allMedicines,'addresses'=>$allAddresses ,'pharmacies'=>$allPharmacies , 'doctors' =>$allDoctors ]);
    }


    public function update(Request $request ,$id)
    {
        
        
        $order = Order::findOrFail($id);
       
    
    $orderTotalPrice=0;
    $medTotalPrices =request()->total_price;
    foreach($medTotalPrices as $medTotalPrice)
    {
        $orderTotalPrice+=intval($medTotalPrice);
    }
    
        $client_id =json_decode(request()->client_name, true)['id']; 
        $medicines =request()->medicine_name;
        
        $medicine_quantity =request()->medicine_qty;
        
        $is_insured =request()->is_insured;
        $doctor_id = request()->doctor_name;
        $pharmacy_id= request()->pharmacy_name;
        $address_id=request()->delivering_address;
   
    $medicine_order = MedicinesOrder::where('order_id',$id)->get();
    $order->update([
        'is_insured'=>$is_insured,
        'total_price'=>$orderTotalPrice,
        'client_id'=>$client_id,
        'pharmacy_id'=>$pharmacy_id,
        'doctor_id'=>$doctor_id,
        'address_id'=>$address_id,
        'status'=>1,
        'creator_type'=>'doctor',
    ]);
   
 
    $medicine_order = MedicinesOrder::where('order_id',$id)->delete();
    for($i = 0 ; $i<count($medicines);$i++){
        $medicine= json_decode($medicines[$i], true);
        $medicine_order=MedicinesOrder::create([
           
            
            'order_id' =>$id,
            'medicine_id' =>$medicine['id'],
            'quantity' =>$medicine_quantity[$i],
    
        ]);
         
     }

        return redirect()->route('orders.index');        
    }

    public function destroy($id)
    {
    $order = Order::findOrFail($id);
    $medicine_order = MedicinesOrder::where('order_id',$id)->delete();
  
    Order::destroy($id);
  
    return redirect()->route('orders.index');
    }
}
