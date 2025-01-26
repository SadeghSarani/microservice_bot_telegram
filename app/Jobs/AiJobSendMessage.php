<?php

namespace App\Jobs;

use App\Service\Ai;
use App\Service\TelegramBot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Telegram\Bot\Laravel\Facades\Telegram;

class AiJobSendMessage implements ShouldQueue
{
    use Queueable;

    private mixed $data;
    private TelegramBot $telegramBot;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->telegramBot = app("telegramBot");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $reply = Ai::sendMessage($this->data['chat'], $this->data['prompt'], $this->data['chat_id']);
            $this->telegramBot->sendMessage($this->data['user_id'], $reply);

        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }

    }
}
