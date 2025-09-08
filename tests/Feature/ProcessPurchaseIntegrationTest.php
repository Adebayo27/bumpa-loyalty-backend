<?php
use App\Jobs\ProcessPurchase;
use App\Models\Achievement;
use App\Models\User;
use App\Models\Purchase;
use App\Services\PaymentGateway;
use Illuminate\Support\Facades\Queue;

// No global event fakes here — we want listeners to run for this integration test

it('saves purchase and triggers cashback when achievement unlocked', function () {
    $user = User::factory()->create();

    // Create an achievement that will be unlocked by this purchase
    Achievement::create([
        'key' => 'test_spend',
        'name' => 'Test Spend',
        'description' => 'Spend 1000 to unlock',
        'rules' => ['type' => 'total_spend', 'target' => 1000],
        'points' => 20,
    ]);

    // Fake gateway to assert sendCashback called using an in-container spy
    $this->app->singleton('cashback.spy', function () {
        return (object) ['called' => false];
    });

    $this->app->bind(PaymentGateway::class, function ($app) {
        $spy = $app->make('cashback.spy');
        return new class($spy) implements PaymentGateway {
            private $spy;
            public function __construct($spy)
            {
                $this->spy = $spy;
            }
            public function sendCashback(int $userId, float $amount, string $currency): bool
            {
                $this->spy->called = true;
                return true;
            }
        };
    });

    // Ensure spy starts false
    $this->assertFalse($this->app->make('cashback.spy')->called);

    $payload = [
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payload' => ['category' => 1],
        'status' => 'done',
    ];

    // Run job synchronously
    ProcessPurchase::dispatchSync($payload);

    $this->assertDatabaseHas('purchases', ['user_id' => $user->id, 'amount' => 1000]);
    // Assert the spy was marked by the fake gateway
    $this->assertTrue($this->app->make('cashback.spy')->called);
});
