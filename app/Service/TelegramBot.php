<?php

namespace App\Service;

use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\DietController;
use App\Http\Controllers\TelegramController;
use App\Models\TelegramReplyKeyboard;
use App\Models\TelegramUser;
use App\Models\TelegramUserLocation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        $this->send($userId, 'به کالری ‌نو خوش آمدید! 💚
برای دریافت رژیم غذایی با هوش مصنوعی، لطفاً از منو گزینه مورد نظر را انتخاب کنید.☺️
(استفاده از کالری ‌نو به منزله موافقت با شرایط و ضوابط از سرویس استفاده است.)');
    }

//    public function NewMessage($message, $location, $allMessage)
//    {
//
//        $telegram_reply_keyboards = TelegramReplyKeyboard::where('title', $message['text'])->first();
//
//        if (isset($allMessage['callback_query']['data'])) {
//            $classInstance = new PackageController();
//            $classInstance->package($message['from']['id'], $allMessage['callback_query']['data']);
//        }
//
//        if (isset($telegram_reply_keyboards->id)) {
//            $classInstance = app($telegram_reply_keyboards->class);
//            $action = $telegram_reply_keyboards->action;
//
//            $classInstance->$action($message['from']['id'], $message['text'], $location);
//        } else {
//            $locationData = TelegramUserLocation::where('telegram_user_id', $message['from']['id'])->first();
//
//            if ($locationData == null || !isset($locationData->location)) {
//                $this->error($message['from']['id'], 'لطفا یکی از دکمه ها را انتخاب کنید');
//            }
//
//            $localData = TelegramReplyKeyboard::query()->where('id', $locationData->location)->first();
//
//            if (isset($localData->class) && $localData->class == ChatBotController::class) {
//                $classInstance = new ChatBotController();
//                $classInstance->chatCreate($message['from']['id'], $message['text'], $locationData->location);
//            } elseif ( !isset($localData->class) || $localData->class == null || !empty($localData->class)) {
//
//                if ($localData == null || !isset($localData->class)) {
//                    $this->error($message['from']['id'], 'لطفا یکی از دکمه ها را انتخاب کنید');
//                }
//
//                $classInstance = app($localData->class);
//                $func = $localData->action;
//
//                Log::error('data : ', [
//                    'func' => $func,
//                    'class' => $classInstance,
//                ]);
//
//                $classInstance->$func($message['from']['id'], $message['text'], $locationData->location);
//            } else {
//                $this->error($message['from']['id'], 'لطفا یکی از دکمه ها را انتخاب کنید');
//            }
//        }
//    }


    public function NewMessage($message, $location, $allMessage)
    {
        /* ----------------------------------------------------------
         * 1️⃣  Callback query – invoked when the bot receives a
         *     data button (e.g. /start)
         * ---------------------------------------------------------- */
        if (!empty($allMessage['callback_query']['data'])) {
            /** @var PackageController $package */
            $package = App::make(PackageController::class);

            // forward the query to the PackageController
            $package->package(
                $message['from']['id'],
                $allMessage['callback_query']['data']
            );

            // no need to touch the other branches
            return;
        }

        /* ----------------------------------------------------------
         * 2️⃣  Find the keyboard that matches the text the user sent
         * ---------------------------------------------------------- */
        $replyKeyboard = TelegramReplyKeyboard::where('title', $message['text'])->first();

        // If we didn't find a matching keyboard → ask user to pick a button
        if ($replyKeyboard === null) {
            $this->error($message['from']['id'], 'لطفا یکی از دکمه ها را انتخاب کنید');
            return;
        }

        $className = $replyKeyboard->class;

        if ($className === \App\Http\Controllers\ChatBotController::class) {
            $classInstance = new ChatBotController();
            $classInstance->chatCreate($message['from']['id'], $message['text'], $location);
            return;
        }

        if (empty($className) || !class_exists($className)) {
            Log::warning('Telegram reply keyboard has an invalid class name', [
                'keyboard_id' => $replyKeyboard->id,
                'title' => $replyKeyboard->title,
                'class' => $className,
            ]);

            $this->error($message['from']['id'], 'لطفا یکی از دکمه ها را انتخاب کنید');
            return;
        }

        /* ----------------------------------------------------------
         * 4️⃣  Resolve the class and invoke the configured action
         * ---------------------------------------------------------- */
        $classInstance = App::make($className);
        $action = $replyKeyboard->action;

        if (!method_exists($classInstance, $action)) {
            Log::warning('Telegram reply keyboard action does not exist', [
                'class' => $className,
                'action' => $action,
            ]);

            $this->error($message['from']['id'], 'دکمهٔ انتخاب شدهٔ نامعتبر است');
            return;
        }

        // call the method – signature is (userId, text, locationId)
        $classInstance->$action(
            $message['from']['id'],
            $message['text'],
            $location
        );

        /* ----------------------------------------------------------
         * 5️⃣  (Optional) - “location” branch
         *     If you still need the extra logic that reads a
         *     TelegramUserLocation record, copy the following
         *     section but keep the exact same guard clauses as
         *     above – always check `empty()`/`class_exists()` before
         *     `app()`.
         * ---------------------------------------------------------- */
        // ...

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

    public function sendNotif($user_id, $text, $location)
    {
        $telegram_reply_keyboards = TelegramReplyKeyboard::where('parent_id', $location)
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
            case 'package':
                $class = new PackageController();
                $class->$function($user_id, $data);
                break;
            default:
                $telegram->sendMessage([
                    'chat_id' => $user_id,
                    'text' => 'انتخاب نادرست '
                ]);
        }
    }

    public function sendDocument($chatId, $documentPath, $caption = '')
    {
        $file = Storage::path($documentPath);

        return Telegram::sendDocument([
            'chat_id' => $chatId,
            'document' => fopen($file, 'r'),
            'caption' => $caption
        ]);
    }
}
