<?php

namespace App\Http\Controllers;

use App\Enums\StatusBayar;
use App\Events\StatsUpdated;
use App\Models\Transaksi;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtransService)
    {
        $serverKey = config('services.midtrans.server_key');
        $signatureKey = $request->input('signature_key');
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Invalid Midtrans webhook signature', [
                'order_id' => $orderId,
                'expected' => $expectedSignature,
                'received' => $signatureKey,
            ]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaksi = Transaksi::where('midtrans_order_id', $orderId)->first();

        if (! $transaksi) {
            Log::warning('Midtrans webhook for unknown order', ['order_id' => $orderId]);

            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        $isSuccess = in_array($transactionStatus, ['capture', 'settlement'])
            && ($fraudStatus === 'accept' || $transactionStatus === 'settlement');
        $isFailure = in_array($transactionStatus, ['deny', 'expire', 'failure']);

        // Idempotency: a repeated success notification for an already-paid order
        // should not re-process or re-dispatch stats events.
        if ($isSuccess && $transaksi->status_bayar === StatusBayar::Lunas) {
            return response()->json(['message' => 'OK'], 200);
        }

        DB::transaction(function () use ($transaksi, $isSuccess, $isFailure) {
            if ($isSuccess) {
                $transaksi->update(['status_bayar' => StatusBayar::Lunas]);
            } elseif ($isFailure) {
                $transaksi->update(['status_bayar' => StatusBayar::Pending]);
            }
        });

        // Dispatch stats update only when the payment actually transitions to Lunas.
        if ($isSuccess && $transaksi->fresh()->status_bayar === StatusBayar::Lunas) {
            try {
                event(new StatsUpdated('sales'));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
