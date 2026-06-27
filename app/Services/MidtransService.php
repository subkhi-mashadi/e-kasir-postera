<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Midtrans\Config;
use Midtrans\CoreApi;

class MidtransService implements PaymentGateway
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

    public function getName(): string
    {
        return 'midtrans';
    }

    public function chargeQris(string $orderId, int $amount, string $customerName): array
    {
        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
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

    public function getStatus(string $orderId): ?object
    {
        try {
            return \Midtrans\Transaction::status($orderId);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function verifySignature(Request $request): bool
    {
        $orderId      = $request->input('order_id');
        $statusCode   = $request->input('status_code');
        $grossAmount  = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . Config::$serverKey);
        return hash_equals($expected, $signatureKey);
    }
}
