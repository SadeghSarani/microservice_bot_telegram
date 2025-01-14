<?php

namespace App\Http\Controllers;

use App\Models\Credit;
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
        $credit = Credit::where('user_id', $user_id)->first();

        if (empty($credit)) {
            $this->telegram->send($user_id, 'متاسفانه در حال حاضر شما اعتباری ندارید');

            return;
        }

        $this->telegram->send($user_id, 'تومان'.$credit->credit.'اعتبار شما در حال حاضر :');

        return;
    }
}
