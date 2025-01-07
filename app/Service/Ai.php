<?php

namespace App\Service;

use App\Models\ChatBot;
use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class Ai
{

    public static function sendMessage($message, $prompt, $chatBotId = null)
    {

        $model = 'google/gemini-2.0-flash-thinking-exp:free';

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
                'answer' => $response->choices[0]['message']['context'] ?? null,
            ]);
        }

        if (!isset($response->choices[0]['message']['context']) || !isset($response->choices[0])) {
            return false;
        }

        return $response->choices[0]['message']['context'];


    }
}