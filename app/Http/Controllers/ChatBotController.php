<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\ChatCreateRequest;
use App\Jobs\AiJobSendMessage;
use App\Models\Credit;
use App\Models\TelegramReplyKeyboard;
use App\Repositories\ChatBotRepository;
use App\Service\Ai;
use App\Service\TelegramBot;
use Illuminate\Http\Request;
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

    public function returnMessage($user_id)
    {
        $this->telegramBot->send($user_id, 'متن مورد نظر را تایپ کنید');
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

        $this->creditActions($telegram_user_id);

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
        ]);

        $this->telegramBot->send($createChat->user_id, 'کاربر گرامی سوال شما دریافت و بعد از پردازش نتیجه برای شما ارسال میشود');
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
            'credit' => $credit->credit - 1000,
        ]);
    }
}
