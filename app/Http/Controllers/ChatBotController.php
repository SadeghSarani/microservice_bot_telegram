<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\ChatCreateRequest;
use App\Jobs\AiJobSendMessage;
use App\Models\Credit;
use App\Models\TelegramReplyKeyboard;
use App\Models\UserPay;
use App\Repositories\ChatBotRepository;
use App\Service\Ai;
use App\Service\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ChatBotController extends Controller
{
    private ChatBotRepository $chatRepo;
    private TelegramBot $telegramBot;

    public function __construct()
    {
        $this->chatRepo = new ChatBotRepository();
        $this->telegramBot = app("telegramBot");
    }

    public function returnMessage($user_id, $text, $loc)
    {

        $message = '';
        switch ($loc) {
            case 9 :
                $message = 'اسم خوراکی یا غذایی که میخوای کالری اش روبدونی بهم بگو! 😊';
                break;
            case 4 :
                $message = 'سوالی درباره رژیم یا تغذیه‌ات داری؟ 🥗 بپرس، من اینجام!😊';
                break;
            case 5 :
                $message = 'یه غذای خوشمزه متناسب با رژیمت می‌خوای؟😊 

باید محدودیت‌های غذایی و علایقت رو بهم بگو تا بتونم پیشنهاد بدم!🥦

👈لطفا به این شکل بهم بگو:

👈مثلا، "رژیم کتو دارم و به ماهی و سبزیجات علاقه دارم."';
                break;
            case 6 :
                $message = 'می‌خوای بدونی غذای امروزت چقدر سالم بوده؟ 📊 اسمشون رو برام بفرست!🥑';
                break;
        }


        $this->telegramBot->send($user_id, $message == '' ? 'سوال خود را بپرسید' : $message);
    }


    public function chatWithoutTelegram(ChatCreateRequest $request)
    {
        $createChat = $this->chatRepo->create([
            'user_id' => $request->input("user_id"),
            'service_id' => $request->input('service_id'),
            'context' => $request->input('context'),
        ]);


        $textPrompt = '';

        collect($createChat->service->prompt)->map(function ($prompt) use (&$textPrompt) {
            $textPrompt .= $prompt->prompt;
        });

        $reply = Ai::sendMessage($createChat->context, $textPrompt, $createChat->id);
    }

    public function chatCreate($telegram_user_id, $text, $location)
    {
        $loc = TelegramReplyKeyboard::where('id', $location)->first();

        if ($loc->service_id == null) {
            $this->telegramBot->send($telegram_user_id, 'لطفا یکی از سرویس های را انتخاب کنید تا بهتر بتونم کمکتون کنم');
            return true;
        }

        $result = $this->checkUserRequest($telegram_user_id);

        if (!$result) {
            return true;
        }

        $createChat = $this->chatRepo->create([
            'user_id' => $telegram_user_id,
            'service_id' => $loc->service_id,
            'context' => $text
        ]);

        $textPrompt = '';

        collect($createChat->service->prompt)->map(function ($prompt) use (&$textPrompt) {
            $textPrompt .= $prompt->prompt;
        });


        AiJobSendMessage::dispatch([
            'chat' => $createChat->context,
            'prompt' => $textPrompt,
            'chat_id' => $createChat->id,
            'user_telegram_id' => $telegram_user_id,
        ])->delay(now()->seconds(20));

        $this->telegramBot->send($telegram_user_id, 'متوجه شدم، 🙂 در حال آماده کردن پاسخ  هستم... ⏳');
        return true;
    }


    private function creditActions($telegram_user_id)
    {
        $credit = Credit::query()->where('user_id', $telegram_user_id)->first();

        if (!$credit) {
            Credit::query()->create([
                'user_id' => $telegram_user_id,
                'credit' => 50000,
            ]);

            return;
        }

        $credit->update([
            'credit' => $credit->credit - 100,
        ]);
    }

    private function checkUserRequest($telegram_user_id)
    {
        $userPackageData = UserPay::query()->where('user_id', $telegram_user_id)->first();

        if ($userPackageData == null) {
            $this->telegramBot->send($telegram_user_id, 'کابرگرامی شما در حال حاضر هیچ پکیج فعالی ندارید');

            return false;
        }

        if ($userPackageData->count <= 0) {
            $this->telegramBot->send($telegram_user_id,
                'کاربرگرامی تعتداد درخواست های پکیج شما به اتمام رسیده لطفا پکیج جدید خریداری نمایید');

            $userPackageData->delete();

            return false;
        }

        if ($userPackageData->expired_at < now()) {
            $this->telegramBot->send($telegram_user_id,
                'کاربرگرامی پکیج شما به اتمام رسیده لطفا پکیج جدید خریداری نمایید');

            $userPackageData->delete();

            return false;
        }


        $userPackageData->update([
            'count' => $userPackageData->count - 1,
        ]);
    }
}
