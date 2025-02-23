<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\UserPay;
use App\Service\TelegramBot;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    private TelegramBot $telegram;

    public function __construct()
    {
        $this->telegram = app('telegramBot');
    }

    public function showCredit($user_id)
    {
        $credit = UserPay::where('user_id', $user_id)->first();

        if (empty($credit)) {
            $this->telegram->send($user_id, 'متاسفانه در حال حاضر شما پکیج فعالی ندارید');

            return;
        }

        $message = 'تعداد درخواست های شما : ' . $credit->count . 'تا تاریخ ' . $credit->expired_at;

        $this->telegram->send($user_id, $message);

        return;
    }
}
