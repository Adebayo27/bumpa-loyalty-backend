<?php
namespace App\Services;

use App\Services\PaymentGateway;

class MockGateway implements PaymentGateway
{
    public function sendCashback(int $userId, float $amount, string $currency): bool
    {
        // Simulate random success/failure
        return rand(0, 1) === 1;
    }
}
