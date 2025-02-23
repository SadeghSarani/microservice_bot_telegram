<?php

namespace App\Http\Controllers;

use App\Service\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    private TelegramBot $telegramBot;
    public function __construct(){
        $this->telegramBot = app("telegramBot");
    }

    public function webHook()
    { // این باید باشد
        return $this->telegramBot->webHook();

    }

    //-------------------------- متد های پاین همه مثال هستند -----------------------------------

    public function newM($telegram_user_id,$text){// دکمه
        $this->telegramBot->send($telegram_user_id,"تو گفتی ".$text);
    }


    public function newMm($telegram_user_id,$text){// دکمه
        $this->telegramBot->send($telegram_user_id,"اهم".$text);
    }

    
    public function bedon($telegram_user_id,$text){

        $this->telegramBot->send($telegram_user_id,"دستی".$text);


        return;
        $this->telegramBot->error($telegram_user_id,"مشکلی پیش آمد به خانه باز گشتیم");
    }

    
    public function newMz($telegram_user_id,$text){

        $this->telegramBot->send($telegram_user_id,"زیر یکم".$text);
    }
}
