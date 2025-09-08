<?php
namespace App\Http\Controllers\Api;

use App\Jobs\ProcessPurchase;
use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'payload' => 'required|array',
            'status' => 'required|string',
        ]);
        ProcessPurchase::dispatch($data);
        return response()->json(['message' => 'Purchase enqueued'], 202);
    }
}
