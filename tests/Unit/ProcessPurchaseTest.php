<?php
use App\Jobs\ProcessPurchase;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('process purchase job enqueues and saves purchase', function () {
    Queue::fake();
    $user = User::factory()->create();
    $payload = [
        'user_id' => $user->id,
        'amount' => 500,
        'currency' => 'USD',
        'payload' => ['category' => 'books'],
        'status' => 'completed',
    ];
    ProcessPurchase::dispatch($payload);
    Queue::assertPushed(ProcessPurchase::class);
});
