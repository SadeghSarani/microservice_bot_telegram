<?php

namespace App\Http\Controllers;

use App\Models\UserPay;
use App\Service\TelegramBot;
use Carbon\Carbon;
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
        $credit = UserPay::where('user_id', $user_id)->where('status', 'active')->first();

        if (empty($credit)) {
            $this->telegram->send($user_id, 'متاسفانه در حال حاضر شما پکیج فعالی ندارید');

            return;
        }

        $expiredAt = Carbon::parse($credit->expired_at);
        $daysRemaining = round(Carbon::now()->diffInDays($expiredAt));

        $message = '
📚 تعداد درخواست باقی مانده:' .  $credit->count .'
⏱ زمان باقی مانده تا پایان اشتراک:'.   $daysRemaining .' روز';



        $this->telegram->send($user_id, $message);

        return;
    }
}
