<?php

namespace App\Service;

use App\Models\ChatBot;
use Carbon\Carbon;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class Ai
{

    public static function sendMessage($message, $prompt, $chatBotId = null)
    {
        if ($chatBotId) {
            $chat = ChatBot::query()->where('id', $chatBotId)->first();
            $chatHistory = ChatBot::query()
                ->where('user_id', $chat->user_id)
                ->where('service_id', $chat->service_id)
                ->where('id', '!=', $chatBotId)
                ->whereBetween('created_at', [now()->subMinutes(30), Carbon::now()]) // Filter by time range
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();


            if ($chat->service_id == 6) {
                $message = 'با توجه به این که ' . $message . ' بهم دستور تهیه ۲ غذا پیشنهاد بده و بگو چجوری باید این غذا ها رو درست کنم؟';
            }

            if ($chat->service_id == 6) {
                $message = 'کالری پروتئین چربی کل کربوهیدرات کل فیبر غذایی قند سدیم ویتامین‌ها و مواد معدنی کلیدی
و  ترکیبات دیگر مثل آنتی‌اکسیدان‌ها، کلسترول، اسید های چرب و...  ' . $message . ' رو بهم بگو و بگو چقدر سالم یا مضر هست واسه سلامتی؟';
            }

            if($chat->service_id == 8) {
                $message = 'فقط به پرامپت و جواب توجه کن';
            }
        }

        $model = \App\Models\Ai::query()->first()['name'] ?? 'openai/gpt-4.1-mini';

        $userMessage = '';

        foreach ($chatHistory as $chatHistoryItem) {
            $userMessage .= $chatHistoryItem->context;
        }


        if (isset($chat) && $chat->service_id == 8) {

            $userChatData = new MessageData(
                [
                    'role' => RoleType::USER,
                    'content' => $userMessage . $message
                ]
            );

        }else {
            $userChatData = new MessageData(
                [
                    'role' => RoleType::USER,
                    'content' => "Here is the chat history: \n" . $userMessage .
                        "\nNow, the user asks: " . $message .
                        "\nIf the current question relies on past conversation, use the history in the response. " .
                        "Otherwise, answer based only on the current question without referencing the history.",
                ]
            );
        }

        $prompt = new MessageData([
            'role' => RoleType::ASSISTANT,
            'content' => $prompt,
        ]);

        $chatData = new ChatData([
            'messages' => [
                $userChatData,
                $prompt,
            ],
            'model' => $model
        ]);


        try {
            $response = LaravelOpenRouter::chatRequest($chatData);
            $answerAi = $response->choices[0]['message']['content'];
            $answer = '';

            if ($chatBotId != null) {

                if ($chat->answer != null) {
                    $answer = $chat->answer;
                }

                ChatBot::where('id', $chatBotId)->update([
                    'answer' => $answerAi . $answer,
                ]);
            }

            return $response->choices[0]['message']['content'];
        } catch (\Exception $exception) {

            throw new \Exception($exception->getMessage());
        }
    }
}
