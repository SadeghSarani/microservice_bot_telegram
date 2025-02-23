<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\UserPay;
use App\Service\TelegramBot;
use App\Service\ZarinPalPayment;
use Carbon\Carbon;
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

        $packages = Package::all()->toArray();

        $data = [];

        foreach ($packages as $package) {
            $data[] = [
                'text' => $package['name'],
                'callback_data' => 'package' . '-' . 'getPackage' . '-' . $package['id'],
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

        $userPay = UserPay::where('user_id', $user_id)->where('status', 'active')->first();

        if ($userPay != null) {
            $this->bot->send($user_id, 'کاربر گرامی شما یک پکیج فعال دارید');

            return true;
        }

        UserPay::updateOrCreate([
            'user_id' => $user_id,
        ],[
            'user_id' => $user_id,
            'package_id' => $message,
            'authority' => $urlPayment['authority'],
            'status' => 'pending',
            'count' => $package['count_request'],
            'expired_at' => Carbon::now()->addMonths((int)$package['month'])->format('Y-m-d H:i:s'),
        ]);

        $message = $package['description'];

        $this->bot->send($user_id, $message);

        $this->bot->createButtonInline($user_id, [
            [
                'text' => 'لینک پرداخت',
                'url' => $urlPayment['url'],
            ]
        ], 'روی لینک پرداخت کلیک نمایید (vpn) خود را خاموش نمایید');

        return true;
    }
}
