<?php
namespace App\Services;

interface PaymentGateway
{
    public function sendCashback(int $userId, float $amount, string $currency): bool;
}
