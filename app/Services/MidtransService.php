<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$paymentIdempotencyKey = uniqid('burjo-');
    }

    public function createQrisCharge(
        string $orderId,
        int $grossAmount,
        array $items = [],
        ?array $customerDetails = null,
    ): array {
        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (string) $grossAmount,
            ],
            'item_details' => $items,
        ];

        if ($customerDetails) {
            $params['customer_details'] = $customerDetails;
        }

        $response = CoreApi::charge($params);

        return (array) $response;
    }

    public function getTransactionStatus(string $transactionId): array
    {
        return (array) Transaction::status($transactionId);
    }

    public function cancelTransaction(string $transactionId): array
    {
        return (array) Transaction::cancel($transactionId);
    }

    public function getQrCodeUrl(array $response): ?string
    {
        $urlKeys = ['qr_code_url', 'qr_code', 'redirect_url'];

        foreach ($urlKeys as $key) {
            if (isset($response[$key]) && is_string($response[$key]) && $response[$key] !== '') {
                return $response[$key];
            }
        }

        if (isset($response['actions']) && is_array($response['actions'])) {
            foreach ($response['actions'] as $action) {
                if (! is_array($action)) {
                    continue;
                }

                $name = $action['name'] ?? '';
                if (in_array($name, ['qr-code', 'generate-qr-code', 'generate-qr-code-v2'], true) && isset($action['url'])) {
                    return $action['url'];
                }
            }
        }

        return null;
    }

    public function getQrString(array $response): ?string
    {
        return $response['qr_string'] ?? null;
    }

    public function getTransactionId(array $response): ?string
    {
        return $response['transaction_id'] ?? null;
    }

    public function isPaymentPaid(array $response): bool
    {
        $status = $response['transaction_status'] ?? '';

        return in_array($status, ['settlement', 'capture'], true);
    }

    public function getRawResponse(): mixed
    {
        return $this->lastResponse ?? null;
    }

    protected mixed $lastResponse = null;
}
