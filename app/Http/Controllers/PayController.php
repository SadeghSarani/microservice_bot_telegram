<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Zarinpal\Zarinpal;

class PayController extends Controller
{
    public function calback(Request $request,Zarinpal $zarinpal){

        $payment = [
            'authority' => $request->input('Authority'), 
            'amount'    => 5000
        ];
        $response = $zarinpal->verify($payment);



        return view('calback');
    }
}
