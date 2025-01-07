<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\ChatCreateRequest;
use App\Repositories\ChatBotRepository;
use App\Service\Ai;
use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    private ChatBotRepository $chatRepo;

    public function __construct()
    {
        $this->chatRepo = new ChatBotRepository();
    }


    public function chatCreate(ChatCreateRequest $request)
    {
        $createChat = $this->chatRepo->create($request->all());

        $textPrompt = '';

        collect($createChat->service->prompt)->map(function ($prompt) use (&$textPrompt) {
            $textPrompt .= $prompt->prompt;
        });

        Ai::sendMessage($createChat->context, $textPrompt, $createChat->id);
    }
}
