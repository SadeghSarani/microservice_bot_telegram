<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Service\TelegramBot;
use App\Service\ZarinPalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Zarinpal\Zarinpal;

class PackageController extends Controller
{

    public function __construct()
    {
        $this->bot = new TelegramBot();
    }

    public function list($user_id, $message)
    {
        $user_id = 854529351;


        Log::error('data : ', [$user_id, $message]);

        $packages = Package::all()->toArray();

        $data = [];

        foreach ($packages as $package) {
            $data[] = [
                'text' => $package['name'],
                'callback_data' => 'package'. '-' . 'getPackage' . '-' . $package['id'],
            ];
        }

        $this->bot->createButtonInline($user_id, $data, 'یکی از پکیج ها را انتخاب نمایید');
    }

    public function getPackage($user_id, $message)
    {
        $package = Package::query()->where('id', $message)->first();

        $zarinpal = new ZarinPalPayment();

        $urlPayment = $zarinpal->payment([
            'amount' => $package['price'],
            'description' => $package['name'],
        ]);

        $message = $package['description'];

        $this->bot->send($user_id, $message);

        $this->bot->createButtonInline($user_id, [
            [
                'text' => 'لینک پرداخت',
                'url' => $urlPayment,
            ]
        ], 'روی لینک پرداخت کلیک نمایید (vpn) خود را خاموش نمایید');

        return true;
    }
}
