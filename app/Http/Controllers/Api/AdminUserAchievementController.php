<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminUserAchievementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['achievements', 'badges']);
        if ($request->has('user_id')) {
            $query->where('id', $request->user_id);
        }
        // Search by name or email with ?filter=value
        if ($request->filled('filter')) {
            $filter = $request->get('filter');
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%")
                  ->orWhere('email', 'like', "%{$filter}%");
            });
        }
        // Optionally return only users who have unlocked achievements
        if ($request->boolean('unlocked_only')) {
            $query->whereHas('achievements', function ($q) {
                $q->whereNotNull('user_achievements.unlocked_at');
            });
        }

        $perPage = (int) $request->get('per_page', 20);
        $users = $query->paginate($perPage);
        return response()->json($users);
    }
}
