<?php

namespace App\Service;

use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TelegramController;
use App\Models\TelegramReplyKeyboard;
use App\Models\TelegramUser;
use App\Models\TelegramUserLocation;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBot
{

    public function webHook()
    {
        $update = Telegram::commandsHandler(true);

        if (isset($update['callback_query'])) {
            $this->setCommand($update['callback_query'], $update['callback_query']['from']['id']);
        }


        if (isset($update['message'])) {
            $message = $update['message'];

            $telegram_reply_keyboards = TelegramReplyKeyboard::where('title', $message['text'])
                ->first();

            if (isset($telegram_reply_keyboards->id)) {
                TelegramUserLocation::updateOrCreate(
                    ['telegram_user_id' => $message['from']['id']],
                    ['location' => $telegram_reply_keyboards->id]
                );
            }

            switch ($message['text']) {
                case '/start':
                    $this->start($message);
                    break;
                default:
                    $this->NewMessage($message, $telegram_reply_keyboards->id ?? null, $update);
                    break;
            }
        }
    }


    public function start($message)
    {
        $userId = $message['from']['id'];
        $username = $message['from']['username'] ?? null;
        $firstName = $message['from']['first_name'] ?? null;
        $lastName = $message['from']['last_name'] ?? null;

        $userExist = TelegramUser::where('user_id', $userId)->count();

        if ($userExist == 0) {
            TelegramUser::create([
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
            ]);
        }

        $this->send($userId, 'به کالری‌نو خوش آمدید! 💚برای دریافت مشاوره تغذیه با هوش مصنوعی، لطفاً سرویس مورد نظرتون را انتخاب کنید.☺️');

    }

    public function NewMessage($message, $location, $allMessage)
    {

        $telegram_reply_keyboards = TelegramReplyKeyboard::where('title', $message['text'])->first();

        if (isset($allMessage['callback_query']['data'])) {
            $classInstance = new PackageController();
            $classInstance->package($message['from']['id'], $allMessage['callback_query']['data']);
        }


        if (isset($telegram_reply_keyboards->id)) {

            $classInstance = app($telegram_reply_keyboards->class);
            $action = $telegram_reply_keyboards->action;

            $classInstance->$action($message['from']['id'], $message['text'], $location);
        } else {
            $locationData = TelegramUserLocation::where('telegram_user_id', $message['from']['id'])->first();
            if (!empty($locationData)) {
                $classInstance = new ChatBotController();
                $classInstance->chatCreate($message['from']['id'], $message['text'], $locationData->location);

            } else {
                $this->error($message['from']['id'], 'لطفا یکی از دکمه ها را انتهاب کنید');
            }
        }
    }

    public function error($user_id, $text)
    {

        TelegramUserLocation::updateOrCreate(
            ['telegram_user_id' => $user_id],
            ['location' => 1]
        );

        $this->send($user_id, $text);

    }

    public function send($user_id, $text)
    {

        $user_location = TelegramUserLocation::where('telegram_user_id', $user_id)
            ->first();

        $telegram_reply_keyboards = TelegramReplyKeyboard::where('parent_id', $user_location->location)
            ->get();

        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true);

        foreach ($telegram_reply_keyboards as $value) {
            $keyboard = $keyboard->row([
                Keyboard::button(['text' => $value->title])
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $user_id,
            'text' => $text,
            'reply_markup' => $keyboard
        ]);
    }

    public function createButtonInline($user_id, $data, $text)
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $keyboard = [
            'inline_keyboard' => [
                $data
            ]
        ];

        $telegram->sendMessage([
            'chat_id' => $user_id,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function setCommand($callback_query, $user_id)
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        [$controller, $function, $data] = explode('-', $callback_query['data']);

        switch ($controller) {
            case 'package' :
                $class = new PackageController();
                $class->$function($user_id, $data);
            break;
            default :
                $telegram->sendMessage([
                    'chat_id' => $user_id,
                    'text' => 'انتخاب نادرست '
                ]);
        }
    }
}
