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
    public int $timeout = 5000;

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
        $countPrompt = count($this->data['prompts']);
        $count = 0;
        while ($countPrompt <= $count) {
            try {
                $chat = $this->data['prompts'][$count];
                $chat .= $this->data['chat'];
                $htmlContent = Ai::sendMessage($chat, 'این چت کاربر برای دریافت رژیم', $this->data['chat_id']);

                if ($count == $countPrompt && $htmlContent) {
                    $this->telegramBot->send(
                        $this->data['user_telegram_id'],
                        "https://app.calorieno.com/diet/user/" . $this->data['chat_id'] .  "
                    💚 #رژیم غذایی ۷ روزه کالری نو!
🥝 از طریق لینک بالا می‌تونی رژیم ات رو‌ مشاهده کنی و در صورتی که نیاز به دریافت PDF داری، کافیه لینک را در مرورگر خودت کپی کنی و از گزینه دریافت PDF استفاده کنی.
از 'کالری' کوچ یادت نره! 😍 تو یک متخصص تغذیه اختصاصی داری که در هر لحظه از شبانه روز کنارت هست و قرار هست در گرفتن رژیم کمکت کنه! 👌 کافیه هر زمان سوال داری از  منو گزینه 'کالری' کوچ رو انتخاب کنی!",
                    );
                }

                $count++;
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
        }
    }
}
