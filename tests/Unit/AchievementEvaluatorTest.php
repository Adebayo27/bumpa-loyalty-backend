<?php
use App\Models\User;
use App\Models\Achievement;
use App\Models\Purchase;
use App\Services\AchievementEvaluator;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementUnlocked;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('achievement evaluator unlocks achievement on total spend', function () {
    Event::fake();
    $user = User::factory()->create();
    $achievement = Achievement::create([
        'key' => 'spender',
        'name' => 'Big Spender',
        'description' => 'Spend 1000',
        'rules' => ['type' => 'total_spend', 'target' => 1000],
        'points' => 100,
    ]);
    Purchase::create([
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'USD',
        'payload' => ['category' => 'electronics'],
        'status' => 'completed',
    ]);
    $evaluator = new AchievementEvaluator($user);
    $evaluator->evaluate($user->purchases()->first());
    Event::assertDispatched(AchievementUnlocked::class);
});
