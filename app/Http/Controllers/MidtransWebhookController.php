<?php

namespace App\Http\Controllers;

use App\Enums\StatusBayar;
use App\Models\Transaksi;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtransService)
    {
        $serverKey = config('services.midtrans.server_key');
        $signatureKey = $request->input('signature_key');
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');

        $expectedSignature = hash('sha512', $orderId.$statusCode.$serverKey.$request->input('transaction_status'));

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Invalid Midtrans webhook signature', [
                'order_id' => $orderId,
                'expected' => $expectedSignature,
                'received' => $signatureKey,
            ]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaksi = Transaksi::where('midtrans_order_id', $orderId)
            ->orWhere('midtrans_transaction_id', $request->input('transaction_id'))
            ->first();

        if (! $transaksi) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            if ($fraudStatus === 'accept' || $transactionStatus === 'settlement') {
                $transaksi->update(['status_bayar' => StatusBayar::Lunas]);
            }
        }

        if (in_array($transactionStatus, ['deny', 'expire', 'failure'])) {
            $transaksi->update(['status_bayar' => StatusBayar::Pending]);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
