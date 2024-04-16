<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Charge;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function checkout(){
        return view('checkout');
    }

    public function session(Request $request)
    {
        \Stripe\Stripe::setApiKey(config('stripe.sk'));

        $productname = $request->get('productname');
        $totalprice = $request->get('total');
//        $two0 = "00";
//        $total = "$totalprice$two0";
//return $totalprice;
        $order_number = $this->generateUniqueIdentifierCode();
        $session = \Stripe\Checkout\Session::create([
            'line_items'  => [
                [
                    'price_data' => [
                        'currency'     => 'AED',
                        'product_data' => [
                            "name" => $productname,
                        ],
                        'unit_amount'  => $totalprice * 100,
                    ],
                    'quantity'   => 1,
                ],

            ],
            'mode'        => 'payment',
            'success_url' => route('success'),
            'cancel_url'  => route('cancel'),
        ]);

//        $charge = Charge::create([
//            'amount' => 1000, // amount in cents
//            'currency' => 'usd',
//            'source' => 'tok_visa', // obtained with Stripe.js
//            'description' => 'Example charge',
//        ]);

       // return $session;
         $order = new Order();
        $order->order_number = $order_number;
        $order->session_id = $session->id;
        $order->amount = $request->get('total');
        $order->save();
        return redirect()->away($session->url);
    }

    /**
     * Generate Unique Identifier. Code
     **/
    public function generateUniqueIdentifierCode()
    {
        do {
            $code = random_int(10000000, 99999999);
        } while (Order::where("order_number", "=", $code)->first());

        return $code;
    }

    public function success()
    {
//      //  return $order_number;
//        $order = Order::where("order_number", "=", $order_number)->first();
//        $paymentId = $order->session_id;
//        \Stripe\Stripe::setApiKey(config('stripe.sk'));
//
//        try {
//            // Retrieve the payment details from Stripe
//            $payment = Charge::retrieve($paymentId);
//            return $payment;
//            // Check the payment status
//            if ($payment->status === 'succeeded') {
//                // Payment is successful
//                return 'Payment is successful!';
//            } else {
//                // Payment is not successful
//                return 'Payment is not successful.';
//            }
//        } catch (\Exception $e) {
//            // Handle any errors
//            return $e->getMessage();
//        }

        return "Thanks for you order You have just completed your payment. The seller will reach out to you as soon as possible";
    }

    public function cancel()
    {
        return "Sorry,Something went wrong. your order is cancelled";
    }

    public function checkPaymentStatus($paymentId)
    {
        \Stripe\Stripe::setApiKey(config('stripe.sk'));

        try {
            // Retrieve the payment details from Stripe
            $payment = Charge::retrieve($paymentId);

            // Check the payment status
            if ($payment->status === 'succeeded') {
                // Payment is successful
                return 'Payment is successful!';
            } else {
                // Payment is not successful
                return 'Payment is not successful.';
            }
        } catch (\Exception $e) {
            // Handle any errors
            return $e->getMessage();
        }
    }
}
