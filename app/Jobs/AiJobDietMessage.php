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
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isFontSubsettingEnabled', true); // Optimize font usage
        $options->set('defaultFont', 'IRANSans'); // Set default font for Persian

        // Specify custom font directory (if fonts are stored in storage/fonts/)
        $options->set('fontDir', asset('fonts/'));
        $options->set('fontCache', asset('fonts/'));

        $dompdf = new Dompdf($options);

        // Define Persian HTML with font and UTF-8 encoding
        $persianHtml = '<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: "IRANSans";
            src: url("' . asset('fonts/Yekan Bakh FaNum Medium.woff2') . '") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        * {
            font-family: "IRANSans", sans-serif;
            text-align: right;
            direction: rtl;
        }
        body {
            padding: 20px;
            line-height: 1.6;
            font-size: 14px;
        }
        h3, h4 {
            color: #2c3e50;
            font-family: "IRANSans", sans-serif;
        }
        ul {
            padding-right: 20px;
        }
    </style>
</head>
<body>' . $html . '</body>
</html>';

        // Ensure HTML is UTF-8 encoded
        $persianHtml = mb_convert_encoding($persianHtml, 'HTML-ENTITIES', 'UTF-8');

        // Load HTML
        $dompdf->loadHtml($persianHtml, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save PDF to storage
        $filename = 'diet_plan_' . time() . '.pdf';
        $path = $filename;

        Storage::put($path, $dompdf->output());
        return $path;
    }

}