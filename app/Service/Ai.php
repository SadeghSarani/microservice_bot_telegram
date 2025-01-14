<?php

namespace App\Service;

use App\Models\ChatBot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class Ai
{

    public static function sendMessage($message, $prompt, $chatBotId = null)
    {

        $model = 'google/gemini-2.0-flash-exp:free';

        $userData = new MessageData(
            [
                'role' => RoleType::USER,
                'content' => $message,
            ],
        );

        $prompt = new MessageData([
            'role' => RoleType::ASSISTANT,
            'content' => $prompt,
        ]);

        $chatData = new ChatData([
            'messages' => [
                $userData,
                $prompt,
            ],
            'model' => $model
        ]);


        $response = LaravelOpenRouter::chatRequest($chatData);

        if ($chatBotId != null) {
            ChatBot::where('id', $chatBotId)->update([
                'answer' => $response->choices[0]['message']['content'],
            ]);
        }

        return $response->choices[0]['message']['content'];
    }
}