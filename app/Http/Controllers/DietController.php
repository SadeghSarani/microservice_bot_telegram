<?php

namespace App\Http\Controllers;

use App\Jobs\AiJobSendMessage;
use App\Models\Diet;
use App\Models\DietUser;
use App\Models\Prompt;
use App\Models\TelegramReplyKeyboard;
use App\Models\TelegramUserLocation;
use App\Models\UserPay;
use App\Repositories\ChatBotRepository;
use App\Service\TelegramBot;
use Illuminate\Support\Facades\Log;

class DietController extends Controller
{
    private TelegramBot $telegramBot;

    public function __construct()
    {
        $this->telegramBot = new TelegramBot();
        $this->chatRepo = new ChatBotRepository();
    }

    public function checkPackage($user_id, $text, $loc)
    {
        $userPay = UserPay::query()->where('user_id', $user_id)
            ->where('package_id', 3)
            ->first();

        if ($userPay == null) {
            
            TelegramUserLocation::query()->where('telegram_user_id', $user_id)->update([
                'location' => TelegramReplyKeyboard::query()->where('title', '/start')->first()->id,
            ]);

            $this->telegramBot->send($user_id, 'شما بسته ای برای دریافت رژیم غذایی خریداری نکردید');
            return true;
        }

        $this->telegramBot->send($user_id, 'ٰرژیم را شروع کنید');

        return true;
    }

    public function dietEnd($user_id, $text, $loc)
    {
        TelegramUserLocation::query()->where('telegram_user_id', $user_id)->update([
            'location' => TelegramReplyKeyboard::query()->where('title', '/start')->first()->id,
        ]);

        $diet = DietUser::where('user_id', $user_id)->first();

        if ($diet == null) {
            $this->telegramBot->send($user_id, 'بازگشت به منوی اصلی');

            return true;
        }

        $diet->delete();
        $this->telegramBot->send($user_id, 'بازگشت به منوی اصلی');

        return true;

    }

    public function dietStart($user_id, $text, $loc)
    {
        $stepCurrentUser = DietUser::where('user_id', $user_id)->first();

        if ($stepCurrentUser == null) {
            $question = Diet::where('question_step', 1)->first();

            DietUser::create([
                'user_id' => $user_id,
                'diet_id' => $question->id,
                'answers_user' => [],
            ]);

            $this->telegramBot->send($user_id, $question->question);
            return true;
        }

        $questionNext = Diet::where('question_step', $stepCurrentUser->diet_id + 1)->first();

        $existingAnswers = $stepCurrentUser->answers_user ?? [];

        $currentQuestion = Diet::where('id', $stepCurrentUser->diet_id)->first();

        $existingAnswers[] = [
            $currentQuestion->question => $text,
        ];

        if ($questionNext == null) {

            $stepCurrentUser->update([
                'answers_user' => $existingAnswers,
            ]);

            $dietData = DietUser::where('user_id', $stepCurrentUser->user_id)->first();
            $this->telegramBot->send($user_id, 'پایان سوالات رژيم ممنون از وقتی که گذاشتید نتیجه رژیم بعد از پردازش ارسال میگردد');

            $prompt = Prompt::where('service_id', 8)->first();
            $promptEntended = $prompt->prompt;

            $answers = $dietData->answers_user ?? [];

            foreach ($answers as $item) {
                foreach ($item as $question => $answer) {
                    $promptEntended .= "\n{$question}: {$answer}";
                }
            }

            $createChat = $this->chatRepo->create([
                'user_id' => $user_id,
                'service_id' => 8,
                'context' => 'دریافت رژیم غذایی',
            ]);


            AiJobSendMessage::dispatch([
                'chat' => $promptEntended,
                'prompt' => $promptEntended,
                'chat_id' => $createChat->id,
                'user_telegram_id' => $user_id,
            ])->delay(now()->seconds(20));

            $dietData->delete();

            return true;
        }

        DietUser::where('user_id', $user_id)->update([
            'diet_id' => $questionNext->id,
            'answers_user' => $existingAnswers,
        ]);

        $this->telegramBot->send($user_id, $questionNext->question);
        return true;

    }

}
