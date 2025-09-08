<?php
namespace App\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGateway
{
    protected $client;
    protected $secret;

    public function __construct($client = null)
    {
        $this->secret = env('PAYSTACK_SECRET') ?: config('services.paystack.secret');
        $this->client = $client ?? new Client([
            'base_uri' => 'https://api.paystack.co/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret,
                'Accept' => 'application/json',
            ],
            'timeout' => 10,
        ]);
    }

    public function sendCashback(int $userId, float $amount, string $currency): bool
    {
        // In Paystack we might transfer to a recipient; for simplicity, assume we have user's recipient code stored in DB
        // This method will attempt a transfer using a mock recipient code for demo
        Log::info("Attempting to send cashback of {$amount} {$currency} to user {$userId}");
    $recipient = env('PAYSTACK_RECIPIENT') ?: 12345678;

        try {
            $response = $this->client->post('transfer', [
                'json' => [
                    'source' => 'balance',
                    'amount' => (int) round($amount * 100), // kobo
                    'recipient' => $recipient,
                    'currency' => $currency,
                    'reference' => 'bl_' . uniqid(),
                    'reason' => 'Cashback for achievement',
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            if (isset($data['status']) && $data['status'] === true) {
                Log::info('Paystack transfer successful', ['response' => $data]);
                return true;
            }

            Log::error('Paystack transfer failed', ['response' => $data]);
            return false;
        } catch (\Exception $e) {
            Log::error('Paystack exception: ' . $e->getMessage());
            return false;
        }
    }
}
