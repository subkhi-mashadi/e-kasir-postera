<?php

namespace App\Services;

use App\Models\Company;
use Midtrans\Config;
use Midtrans\CoreApi;

class MidtransService
{
    public function __construct(?Company $company = null)
    {
        $serverKey = ($company?->midtrans_server_key) ?: config('midtrans.server_key');
        $clientKey = ($company?->midtrans_client_key) ?: config('midtrans.client_key');
        $isProd    = ($company?->midtrans_server_key)
            ? (bool) $company->midtrans_is_production
            : config('midtrans.is_production');

        Config::$serverKey    = $serverKey;
        Config::$clientKey    = $clientKey;
        Config::$isProduction = $isProd;
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    /**
     * Create a QRIS charge via Midtrans Core API.
     * Returns ['qr_url' => string, 'order_id' => string] on success.
     */
    public function chargeQris(string $orderId, int $amount, string $customerName): array
    {
        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'customer_details' => [
                'first_name' => $customerName,
            ],
        ];

        $response = CoreApi::charge($payload);

        $qrUrl = null;
        if (isset($response->actions)) {
            foreach ($response->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $qrUrl = $action->url;
                    break;
                }
            }
        }

        return [
            'order_id'    => $response->order_id,
            'qr_url'      => $qrUrl,
            'status'      => $response->transaction_status ?? 'pending',
            'raw'         => $response,
        ];
    }

    /**
     * Get transaction status from Midtrans.
     */
    public function getStatus(string $orderId): ?object
    {
        try {
            return \Midtrans\Transaction::status($orderId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify webhook notification signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . Config::$serverKey);
        return hash_equals($expected, $signatureKey);
    }
}
