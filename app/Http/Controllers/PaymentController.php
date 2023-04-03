<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index() {
        return view('payments.index');
    }

    public function checkout() {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $order_id = 2;


        $orderDetails = DB::table('medicines_orders')->where('order_id', $order_id)->get();
        $line_items = [];
        foreach($orderDetails as $order) {
            $medicineItem = Medicine::find($order->medicine_id);
            
            $medicineName = $medicineItem->name;
            $medicinePrice = $medicineItem->price;
            $quantity = $order->quantity;
            $orderItem = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $medicineName,
                    ],
                    'unit_amount' => $medicinePrice,
                    ],
                    'quantity' => $quantity,
                ];
            $line_items[] = $orderItem; 
        }

        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => [$line_items],
            'mode' => 'payment',
            'success_url' => route('payments.success', [], true),
            'cancel_url' => route('payments.cancel', [], true),
        ]);

        return redirect($checkout_session->url);
    }

    public function success() {
        return view("payments.success");
    }

    public function cancel() {
        return redirect()->route('orders.index');
    }
}
