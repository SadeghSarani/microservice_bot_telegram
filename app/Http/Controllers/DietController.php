<?php

namespace App\Http\Controllers;

use App\Jobs\AiJobDietMessage;
use App\Jobs\AiJobSendMessage;
use App\Models\ChatBot;
use App\Models\Diet;
use App\Models\DietUser;
use App\Models\Package;
use App\Models\Prompt;
use App\Models\TelegramReplyKeyboard;
use App\Models\TelegramUserLocation;
use App\Models\UserPay;
use App\Repositories\ChatBotRepository;
use App\Service\TelegramBot;
use App\Service\ZarinPalPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Request;

class DietController extends Controller
{
    private TelegramBot $telegramBot;
    private ChatBotRepository $chatRepo;

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

        $this->telegramBot->send($user_id, 'فقط چند قدم تا دریافت رژیم و شروع مسیر سلامتی باقی مونده! 😊
💚 در ادامه چند سوال ازت پرسیده میشه و براساس اونا رژیم در اختیارت قرار میگیره. 
⭕️ در صورتی که پاسخ سوالی رو نداری،کلمه "ندارم" رو وارد کن و به سوال بعدی برو.
👈 برای دریافت رژیم، گزینه دریافت رژیم رو در منو انتخاب کن.');
        return true;
    }

    public function loadPage(Request $request, $chat)
    {
        $chat = ChatBot::query()->find($chat);

        return view('diet_user', ['content' => $chat['answer']]);
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

        if ($stepCurrentUser != null && $stepCurrentUser->created_at > Carbon::now()->addMinutes(5)) {
            $stepCurrentUser->delete();
            $question = Diet::where('question_step', 1)->first();

            DietUser::create([
                'user_id' => $user_id,
                'diet_id' => $question->id,
                'answers_user' => [],
            ]);

            $this->telegramBot->send($user_id, $question->question);
            return true;
        }

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

            $prompt = Prompt::where('service_id', 8)->first();
            $promptEntended = $prompt->prompt;

            $answers = $dietData->answers_user ?? [];

            foreach ($answers as $item) {
                foreach ($item as $question => $answer) {
                    $promptEntended .= "\n{$question}: {$answer}";
                }
            }


            $this->setPackage($user_id);

            return true;
            //            $createChat = $this->chatRepo->create([
            //                'user_id' => $user_id,
            //                'service_id' => 8,
            //                'context' => 'دریافت رژیم غذایی',
            //            ]);
            //
            //
            //            AiJobDietMessage::dispatch([
            //                'chat' => $promptEntended,
            //                'prompt' => $promptEntended,
            //                'chat_id' => $createChat->id,
            //                'user_telegram_id' => $user_id,
            //            ])->delay(now()->seconds(20));

            //            $dietData->delete();

        }

        DietUser::where('user_id', $user_id)->update([
            'diet_id' => $questionNext->id,
            'answers_user' => $existingAnswers,
        ]);

        $this->telegramBot->send($user_id, $questionNext->question);
        return true;
    }


    public function setPackage($user_id)
    {
        $package = Package::query()->where('additional', 'diet')->first();
        $zarinpal = new ZarinPalPayment();

        $urlPayment = $zarinpal->payment([
            'amount' => $package['price'],
            'description' => $package['name'],
        ]);

        $userPay = UserPay::where('user_id', $user_id)
            ->where('package_id', $package['id'])
            ->where('status', 'active')->first();

        if ($userPay != null) {
            $userPay->delete();

            UserPay::updateOrCreate([
                'user_id' => $user_id,
            ], [
                'user_id' => $user_id,
                'package_id' => $package->id,
                'authority' => $urlPayment['authority'],
                'status' => 'pending',
                'count' => $package['count_request'],
                'expired_at' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s'),
            ]);

            $message = $package['description'];
            $this->telegramBot->send($user_id, $message);
            $this->telegramBot->createButtonInline($user_id, [
                [
                    'text' => '✅ لینک پرداخت',
                    'url' => $urlPayment['url'],
                ]
            ], '👈 ابتدا فیلتر شکن (vpn) خود را خاموش نمایید.
سپس روی لینک پرداخت کلیک نمایید و پرداخت را انجام دهید.👇');
            return true;
        } else {

            UserPay::updateOrCreate([
                'user_id' => $user_id,
            ], [
                'user_id' => $user_id,
                'package_id' => $package->id,
                'authority' => $urlPayment['authority'],
                'status' => 'pending',
                'count' => $package['count_request'],
                'expired_at' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s'),
            ]);

            $message = $package['description'];
            $this->telegramBot->send($user_id, $message);
            $this->telegramBot->createButtonInline($user_id, [
                [
                    'text' => '✅ لینک پرداخت',
                    'url' => $urlPayment['url'],
                ]
            ], '👈 ابتدا فیلتر شکن (vpn) خود را خاموش نمایید.
سپس روی لینک پرداخت کلیک نمایید و پرداخت را انجام دهید.👇');

            return true;
        }
    }
}
