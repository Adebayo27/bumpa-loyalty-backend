<?php
namespace App\Jobs;

use App\Models\Purchase;
use App\Services\AchievementEvaluator;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessPurchase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        $purchase = Purchase::create($this->payload);
        $evaluator = new AchievementEvaluator($purchase->user);
        $evaluator->evaluate($purchase);
    }
}
