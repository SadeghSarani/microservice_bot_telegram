<?php

namespace App\Http\Controllers;

use App\Jobs\AiJobDietMessage;
use App\Models\DietUser;
use App\Models\Package;
use App\Models\Prompt;
use App\Models\UserPay;
use App\Repositories\ChatBotRepository;
use App\Service\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayController extends Controller
{
    private ChatBotRepository $chatRepo;
    private TelegramBot $telegramBot;

    public function __construct()
    {
        $this->chatRepo = new ChatBotRepository();
        $this->telegramBot = new TelegramBot();
    }

    public function calback(Request $request)
    {
        $authority = $request->query('Authority');

        $userPay = UserPay::where('authority', $authority)
        ->where('status', 'pending')
        ->first();

        if (!$userPay) {
            return view('callback-error');
        }

        $amount = Package::where('id', $userPay['package_id'])->first();

        $data = [
            "merchant_id" => env('ZARINPAL_MERCHANTID'),
            "authority" => $_GET['Authority'],
            "amount" => $amount['price']
        ];

        $response = Http::withHeaders([
            'User-Agent' => 'ZarinPal Rest Api v4',
            'Content-Type' => 'application/json'
        ])->post('https://payment.zarinpal.com/pg/v4/payment/verify.json', $data);
    
        $result = $response->json();

        if (isset($result['data']['code']) && $result['data']['code'] == 100) {
            $userPay->update([
                'status' => 'active',
            ]);

            $createChat = $this->chatRepo->create([
                'user_id' => $userPay['user_id'],
                'service_id' => 8,
                'context' => 'دریافت رژیم غذایی',
            ]);
            $prompt = Prompt::where('service_id', 8)->first();
            $promptEntended = $prompt->prompt;

            $dietData = DietUser::where('user_id', $userPay['user_id'])->first();
            $answers = $dietData->answers_user ?? [];

            foreach ($answers as $item) {
                foreach ($item as $question => $answer) {
                    $promptEntended .= "\n{$question}: {$answer}";
                }
            }

            AiJobDietMessage::dispatch([
                'chat' => $promptEntended,
                'prompt' => $promptEntended,
                'chat_id' => $createChat->id,
                'user_telegram_id' => $userPay['user_id'],
            ])->delay(now()->seconds(20));

            $this->telegramBot->sendNotif($userPay['user_id'], 'کاربر گرامی نتیجه رژیم شما پس از پردازش برای شما ارسال خواهد شد', 1);

            return view('calback');


        } else {
            return view('callback-failed');
        }

    }
}
