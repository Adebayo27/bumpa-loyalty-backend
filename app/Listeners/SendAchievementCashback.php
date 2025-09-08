<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Services\PaymentGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendAchievementCashback implements ShouldQueue
{
    public function handle(AchievementUnlocked $event)
    {
        $achievement = $event->userAchievement->achievement;
        $user = $event->userAchievement->user;
        // Example: only send cashback for achievements with points >= 100
        if ($achievement->points >= 20) {
            $gateway = app(PaymentGateway::class);
            $ok = $gateway->sendCashback($user->id, 10, 'NGN');
            if (!$ok) {
                // Failure scenario
                Log::warning("Failed to send cashback to user {$user->id} for achievement {$achievement->id}");
            } else {
                Log::info("User {$user->id} unlocked achievement {$achievement->name} and received cashback.");
            }
        }
    }
}
