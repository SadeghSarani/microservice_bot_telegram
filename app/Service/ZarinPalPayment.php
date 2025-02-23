<?php

namespace App\Service;

use App\Exceptions\ApiException;
use App\Exceptions\GatewayException;
use Exception;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Zarinpal\Zarinpal;

class ZarinPalPayment
{

    private $url = 'https://sandbox.zarinpal.com/pg';
    const VERIFY_API = '/v4/payment/verify.json';
    const START_PAY = '/StartPay/';
    const PAYMENT_API = '/v4/payment/request.json';
    const SUCCESS_STATUS = 'OK';
    const UNSUCCESS_STATUS = 'NOK';


    public function payment($data)
    {
        $paymentData = [
            'callback_url' => route('payment.callback'),
            'amount' => $data['amount'],
            'description' => $data['description'],
        ];

        try {
            $response = Http::acceptJson()->post($this->url . self::PAYMENT_API, $paymentData);

            if ($response->status() != Response::HTTP_OK || $response->json()['data']['code'] != 100) {

                throw new Exception('gateway_has_error', Response::HTTP_BAD_REQUEST);
            }

            return $this->url . self::START_PAY . $response->json()['data']['authority'];

        } catch (Exception $ex) {

            throw new \Exception($ex->getMessage());
        }
    }


}