<?php

namespace App\Jobs;

use App\Service\Ai;
use App\Service\TelegramBot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AiJobSendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private mixed $data;
    private TelegramBot $telegramBot;
    public int $tries = 1000000000;

    public function __construct($data)
    {
        $this->data = $data;
        $this->telegramBot = app("telegramBot");
    }

    public function retryUntil()
    {
        return now()->addMinutes(2); // Allow retries for the next 5 minutes
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $reply = Ai::sendMessage($this->data['chat'], $this->data['prompt'], $this->data['chat_id']);
            $this->telegramBot->send($this->data['user_telegram_id'], $reply);

        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }

    }
}
