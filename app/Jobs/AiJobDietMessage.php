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
        // Configure DOMPDF options for Persian support
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true); // Important for RTL support
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'dejavu-sans'); // Supports Persian characters

        // If you want to use a custom Persian font:
        // $options->set('fontDir', storage_path('fonts'));
        // $options->set('fontCache', storage_path('fonts'));
        // $options->set('defaultFont', 'your-persian-font'); // e.g., 'xb-zar', 'b-nazanin'

        $dompdf = new Dompdf($options);

        $persianHtml = '<html dir="rtl">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <style>
                    body {
                        font-family: dejavu-sans;
                        text-align: right;
                        direction: rtl;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        direction: rtl;
                    }
                    th, td {
                        padding: 8px;
                        text-align: right;
                    }
                </style>
            </head>
            <body>' . $html . '</body>
        </html>';

        $dompdf->loadHtml($persianHtml, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'diet_plan_' . time() . '.pdf';
        $path = $filename;

        Storage::put($path, $dompdf->output());

        return $path;
    }
}