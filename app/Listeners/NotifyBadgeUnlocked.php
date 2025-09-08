<?php
namespace App\Listeners;

use App\Events\BadgeUnlocked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyBadgeUnlocked implements ShouldQueue
{
    public function handle(BadgeUnlocked $event)
    {
        // Keep server-side processing (e.g., email, analytics) here.
        $user = $event->userBadge->user;
        $badge = $event->userBadge->badge;

        // Log as a fallback and record server-side metrics.
        Log::info("User {$user->id} unlocked badge {$badge->name}");
    }
}
