<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserAchievementController extends Controller
{
    public function index(User $user)
    {
        $achievements = $user->achievements()->withPivot('progress', 'unlocked_at', 'meta')->get();
        $badges = $user->badges()->withPivot('unlocked_at')->get();
        return response()->json([
            'achievements' => $achievements,
            'badges' => $badges,
        ]);
    }
}
