<?php
use App\Services\PaystackGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

uses(Tests\TestCase::class);

it('returns true on successful transfer response', function () {
    $body = json_encode(['status' => true, 'message' => 'Transfer queued']);
    $mockClient = new class($body) {
        protected $body;
        public function __construct($body)
        {
            $this->body = $body;
        }
        public function post($uri = '', array $options = [])
        {
            return new Response(200, ['Content-Type' => 'application/json'], $this->body);
        }
    };

    $gateway = new PaystackGateway($mockClient);
    // Ensure PAYSTACK_RECIPIENT env is set for test
    putenv('PAYSTACK_RECIPIENT=test_recipient');
    $ok = $gateway->sendCashback(1, 100, 'NGN');
    expect($ok)->toBeTrue();
});
