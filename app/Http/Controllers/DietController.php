<?php

namespace App\Http\Controllers;

use App\Models\Diet;
use App\Models\DietUser;
use App\Models\TelegramReplyKeyboard;
use App\Models\TelegramUserLocation;
use App\Service\TelegramBot;
use Illuminate\Support\Facades\Log;

class DietController extends Controller
{
    private TelegramBot $telegramBot;

    public function __construct()
    {
        $this->telegramBot = new TelegramBot();
    }

    public function checkPackage($user_id, $text, $loc)
    {
        $this->telegramBot->send($user_id, 'ٰرژیم را شروع کنید');
        return true;
    }

    public function dietEnd($user_id, $text, $loc)
    {
        TelegramUserLocation::query()->where('telegram_user_id', $user_id)->update([
            'location' => TelegramReplyKeyboard::query()->where('title', '/start')->first()->id,
        ]);

        $this->telegramBot->send($user_id, 'بازگشت به منوی اصلی');

        return true;
    }

    public function dietStart($user_id, $text, $loc)
    {
        $stepCurrentUser = DietUser::where('user_id', $user_id)->first();

        if ($stepCurrentUser == null) {

            $question = Diet::where('question_step', 1)->first();

            DietUser::query()->create([
                'user_id' => $user_id,
                'diet_id' => $question->id,
            ]);

            $this->telegramBot->send($user_id, $question->question);

            return true;
        }

        $stepCurrentUser = DietUser::where('user_id', $user_id)->first();


        $questionNext = Diet::query()->where('question_step', $stepCurrentUser->diet_id + 1)
            ->first();




        if ($questionNext == null) {

            $answers = $stepCurrentUser->answer_user;
            $questionCurren = Diet::query()->where('question_step', $stepCurrentUser->diet_id);
            $answers[$questionCurren->question] = $text;
            $stepCurrentUser->update([
                'answers_user' => $answers,
            ]);
            DietUSer::query()->where('user_id', $stepCurrentUser->user_id)->delete();
            $this->telegramBot->send($user_id, 'پایان سوالات رژيم ممنون از وقتی که گذاشتید نتیجه رژیم بعد از پردازش ارسال میگردد');

            return true;
        }

        DietUser::where('user_id', $user_id)->update([
            'diet_id' => $questionNext->id,
        ]);

        $answers = $stepCurrentUser->answer_user;
        $answers[$questionNext->question] = $text;

        $stepCurrentUser->update([
            'answers_user' => $answers,
        ]);

        $this->telegramBot->send($user_id, $questionNext->question);

        return true;
    }

}
