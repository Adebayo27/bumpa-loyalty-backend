<?php
namespace App\Services;

use App\Models\User;
use App\Models\Purchase;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Support\Carbon;

class AchievementEvaluator
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function evaluate(Purchase $purchase)
    {
        $achievements = Achievement::all();
        foreach ($achievements as $achievement) {
            $rules = $achievement->rules;
            $progress = $this->calculateProgress($rules);
            $userAchievement = UserAchievement::firstOrNew([
                'user_id' => $this->user->id,
                'achievement_id' => $achievement->id,
            ]);
            $userAchievement->progress = $progress;
            if ($progress >= ($rules['target'] ?? 1) && !$userAchievement->unlocked_at) {
                $userAchievement->unlocked_at = Carbon::now();
                $userAchievement->save();
                event(new AchievementUnlocked($userAchievement));
                $this->checkBadges($userAchievement);
            } else {
                $userAchievement->save();
            }
        }
    }

    protected function calculateProgress(array $rules)
    {
        // Example: total_spend, orders_count, category_count
        $progress = 0;
        if (isset($rules['type'])) {
            switch ($rules['type']) {
                case 'total_spend':
                    $progress = $this->user->purchases()->sum('amount');
                    break;
                case 'orders_count':
                    $progress = $this->user->purchases()->count();
                    break;
            }
        }
        return $progress;
    }

    protected function checkBadges()
    {
        $badges = Badge::all();
        foreach ($badges as $badge) {
            $criteria = $badge->criteria;
            $unlocked = false;
            // Example: check if user has unlocked enough achievements for badge
            if (isset($criteria['achievements_count'])) {
                $count = UserAchievement::where('user_id', $this->user->id)
                    ->whereNotNull('unlocked_at')->count();
                if ($count >= $criteria['achievements_count']) {
                    $unlocked = true;
                }
            }
            if ($unlocked) {
                $userBadge = UserBadge::firstOrNew([
                    'user_id' => $this->user->id,
                    'badge_id' => $badge->id,
                ]);
                if (!$userBadge->unlocked_at) {
                    $userBadge->unlocked_at = Carbon::now();
                    $userBadge->save();
                    event(new BadgeUnlocked($userBadge));
                }
            }
        }
    }
}
