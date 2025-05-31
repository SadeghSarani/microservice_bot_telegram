<?php

namespace App\Jobs;

use App\Service\Ai;
use App\Service\TelegramBot;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AiJobDietMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $data;
    private TelegramBot $telegramBot;
    public int $tries = 30;
    public int $timeout = 1200;

    public function __construct($data)
    {
        $this->data = $data;
        $this->telegramBot = app("telegramBot");
    }

    public function retryUntil()
    {
        return now()->addMinutes(2);
    }

    public function handle(): void
    {
        try {
            $htmlContent = Ai::sendMessage($this->data['chat'], $this->data['prompt'], $this->data['chat_id']);
            if ($htmlContent) {
                $this->telegramBot->send(
                    $this->data['user_telegram_id'],
                    "https://calorieno.com/diet/user".$this->data['chat_id']."کاربر گرامی رژیم شما آماده شده است و از طریق لینک زیر اقدام نمایید نسبت به دیدن و ذخیره آن با تشکر",
                );
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}