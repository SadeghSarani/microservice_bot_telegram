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

            $pdfPath = $this->generatePdf($htmlContent);

            $this->telegramBot->sendDocument(
                $this->data['user_telegram_id'],
                $pdfPath,
                'Here is your diet plan'
            );

            Storage::delete($pdfPath);

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    private function generatePdf(string $html): string
    {
        // Configure DOMPDF options
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate a unique filename
        $filename = 'diet_plan_' . time() . '.pdf';
        $path = $filename;

        // Save the PDF to storage
        Storage::put($path, $dompdf->output());

        return $path;
    }
}