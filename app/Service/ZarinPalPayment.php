<?php

namespace App\Service;

use App\Exceptions\ApiException;
use App\Exceptions\GatewayException;
use Exception;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Zarinpal\Clients\GuzzleClient;
use Zarinpal\Zarinpal;

class ZarinPalPayment
{

    private $url = 'https://payment.zarinpal.com/pg/StartPay';
    const VERIFY_API = '/v4/payment/verify.json';
    const START_PAY = '/StartPay/';
    const PAYMENT_API = '/v4/payment/request.json';
    const SUCCESS_STATUS = 'OK';
    const UNSUCCESS_STATUS = 'NOK';


    public function payment($data)
    {
        $paymentData = [
            'callback_url' => route('pay.calback'),
            'amount' => $data['amount'],
            'description' => $data['description'],
        ];

        $sandbox = true;
        $zarinGate = false; // OR true
        $zarinGatePSP = 'Asan'; // Leave this parameter blank if you don't need a custom PSP zaringate.
        $client = new GuzzleClient($sandbox);
        $lang = 'fa'; // OR en

        $zarinpal = new Zarinpal(env('ZARINPAL_MERCHANTID'), $client, $lang, $sandbox, $zarinGate, $zarinGatePSP);

        try {
            $response = $zarinpal->request($paymentData);
            $code = $response['data']['code'];
            $message = $zarinpal->getCodeMessage($code);
            if($code === 100) {

                return [
                    'url' => $this->url.'/'.$response['data']['authority'],
                    'authority' => $response['data']['authority'],
                ];
            }
        } catch (Exception $ex) {

            throw new \Exception($ex->getMessage());
        }
    }


}