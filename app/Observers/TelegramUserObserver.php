<?php

namespace App\Observers;

use App\Models\TelegramUser;
use App\Models\UserPay;

class TelegramUserObserver
{
    /**
     * Handle the TelegramUser "created" event.
     */
    public function created(TelegramUser $telegramUser): void
    {
        UserPay::create([
            'user_id' => $telegramUser->id,
            'package_id' => 0,
            'authority' => '',
            'status' => 'active',
            'count' => 100,
            'expired_at' => now()->addDays(7)
        ]);
    }
    
    public function updated(TelegramUser $telegramUser): void
    {
        //
    }

    /**
     * Handle the TelegramUser "deleted" event.
     */
    public function deleted(TelegramUser $telegramUser): void
    {
        //
    }

    /**
     * Handle the TelegramUser "restored" event.
     */
    public function restored(TelegramUser $telegramUser): void
    {
        //
    }

    /**
     * Handle the TelegramUser "force deleted" event.
     */
    public function forceDeleted(TelegramUser $telegramUser): void
    {
        //
    }
}
