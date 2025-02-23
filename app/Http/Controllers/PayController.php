<?php

namespace App\Http\Controllers;

use App\Models\UserPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayController extends Controller
{
    public function calback(Request $request){


        $authority = $request->query('Authority');

        $userPay = UserPay::where('authority', $authority)
        ->where('status', 'pending')
        ->first();

        if (!$userPay) {
            return 'پرداخت نا موفق';
        }
        


        $data = [
            "merchant_id" => env('ZARINPAL_MERCHANTID'),
            "authority" => $_GET['Authority'],
            "amount" => $userPay['price']
        ];
        $response = Http::withHeaders([
            'User-Agent' => 'ZarinPal Rest Api v4',
            'Content-Type' => 'application/json'
        ])->post('https://sandbox.zarinpal.com/pg/v4/payment/verify.json', $data);
    
        $result = $response->json();
    
        
    
        if (isset($result['data']['code']) && $result['data']['code'] == 100) {

            
            $userPay->update([
                'status' => 'active',
            ]);
            return view('calback');

        } else {
            return 'نا موفق';
        }



    }
}
