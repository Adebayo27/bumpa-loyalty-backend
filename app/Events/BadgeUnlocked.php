<?php
namespace App\Events;

use App\Models\UserBadge;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeUnlocked implements ShouldBroadcast
{
    use Dispatchable, SerializesModels, InteractsWithSockets;

    public $userBadge;
    public $userId;
    public $badge;
    public $unlockedAt;

    public function __construct(UserBadge $userBadge)
    {
        $this->userBadge = $userBadge;
        $this->userId = $userBadge->user_id;
        $this->badge = [
            'id' => $userBadge->badge->id ?? $userBadge->badge_id,
            'name' => $userBadge->badge->name ?? null,
            'icon' => $userBadge->badge->icon ?? null,
        ];
        $this->unlockedAt = $userBadge->unlocked_at;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("users.{$this->userId}.badges");
    }

    /**
     * The data to broadcast with the event.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'badge' => $this->badge,
            'unlocked_at' => $this->unlockedAt,
        ];
    }
    
}
