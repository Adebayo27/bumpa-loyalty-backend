<?php
namespace App\Events;

use App\Models\UserAchievement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked implements ShouldBroadcast
{
    use Dispatchable, SerializesModels, InteractsWithSockets;

    public $userAchievement;
    public $userId;
    public $achievement;
    public $unlockedAt;

    public function __construct(UserAchievement $userAchievement)
    {
        $this->userAchievement = $userAchievement;
        $this->userId = $userAchievement->user_id;
        $this->achievement = [
            'id' => $userAchievement->achievement->id ?? $userAchievement->achievement_id,
            'key' => $userAchievement->achievement->key ?? null,
            'name' => $userAchievement->achievement->name ?? null,
            'points' => $userAchievement->achievement->points ?? null,
        ];
        $this->unlockedAt = $userAchievement->unlocked_at;
    }
    

    public function broadcastOn()
    {
        return new Channel("users.{$this->userId}.achievements");
    }

    public function broadcastWith()
    {
        return [
            'achievement' => $this->achievement,
            'unlocked_at' => $this->unlockedAt,
        ];
    }
}
