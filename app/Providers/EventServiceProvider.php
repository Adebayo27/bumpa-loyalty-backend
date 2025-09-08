<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Listeners\SendAchievementCashback;
use App\Listeners\NotifyBadgeUnlocked;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AchievementUnlocked::class => [
            SendAchievementCashback::class,
        ],
        BadgeUnlocked::class => [
            NotifyBadgeUnlocked::class,
        ],
    ];
}
