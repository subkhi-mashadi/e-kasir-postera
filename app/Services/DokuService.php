<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DokuService implements PaymentGateway
{
    private ?string $clientId;
    private ?string $secretKey;
    private bool $isProduction;
    private string $baseUrl;

    public function __construct(?Company $company = null)
    {
        $this->clientId     = ($company?->doku_client_id) ?: config('doku.client_id');
        $this->secretKey    = ($company?->doku_secret_key) ?: config('doku.secret_key');
        $this->isProduction = ($company?->doku_client_id)
            ? (bool) $company->doku_is_production
            : config('doku.is_production');
        $this->baseUrl      = $this->isProduction
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    public function getName(): string
    {
        return 'doku';
    }

    private function ensureConfigured(): void
    {
        if (!$this->clientId || !$this->secretKey) {
            throw new \RuntimeException('DOKU tidak dikonfigurasi. Atur Client ID & Secret Key di Pengaturan Pembayaran.');
        }
    }

    private function buildSignature(string $method, string $target, string $body = '', ?string $requestId = null): array
    {
        $requestId = $requestId ?? (string) Str::uuid();
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');

        $components = "Client-Id:{$this->clientId}\nRequest-Id:{$requestId}\nRequest-Timestamp:{$timestamp}\nRequest-Target:{$target}";

        if ($body && $body !== '') {
            $digest = base64_encode(hash('sha256', $body, true));
            $components .= "\nDigest:{$digest}";
        }

        $signature = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $components, $this->secretKey, true)
        );

        return [
            'Client-Id'         => $this->clientId,
            'Request-Id'        => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature'         => $signature,
            'Digest'            => $digest ?? null,
        ];
    }

    public function chargeQris(string $orderId, int $amount, string $customerName): array
    {
        $this->ensureConfigured();

        $baseUrl = url('/');
        $body = [
            'order' => [
                'amount'          => $amount,
                'invoice_number'  => $orderId,
                'currency'        => 'IDR',
                'callback_url'    => "{$baseUrl}/order-submitted",
                'callback_url_cancel' => $baseUrl,
                'callback_url_result' => "{$baseUrl}/order-submitted",
                'auto_redirect'   => true,
            ],
            'payment' => [
                'payment_due_date' => 60,
            ],
            'customer' => [
                'name'  => $customerName,
            ],
            'additional_info' => [
                'override_notification_url' => "{$baseUrl}/webhook/doku",
            ],
        ];

        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        $sigHeaders = $this->buildSignature('POST', '/checkout/v1/payment', $bodyJson);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->baseUrl}/checkout/v1/payment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Client-Id: {$sigHeaders['Client-Id']}",
            "Request-Id: {$sigHeaders['Request-Id']}",
            "Request-Timestamp: {$sigHeaders['Request-Timestamp']}",
            "Signature: {$sigHeaders['Signature']}",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \RuntimeException("DOKU error {$httpCode}: {$rawResponse}");
        }

        $body = json_decode($rawResponse);
        $data = $body->response ?? $body->data ?? $body;
        $payment = $data->payment ?? $body->payment ?? null;
        $paymentUrl = $data->payment_url ?? $payment->url ?? $payment->payment_url ?? $data->url ?? null;

        return [
            'order_id'     => $orderId,
            'qr_url'       => $paymentUrl,
            'redirect_url' => $paymentUrl,
            'status'       => 'pending',
            'raw'          => $body,
        ];
    }

    public function getStatus(string $orderId): ?object
    {
        try {
            $this->ensureConfigured();

            $target    = "/orders/v1/status/{$orderId}";
            $sigHeaders = $this->buildSignature('GET', $target, '');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "{$this->baseUrl}{$target}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Client-Id: {$sigHeaders['Client-Id']}",
                "Request-Id: {$sigHeaders['Request-Id']}",
                "Request-Timestamp: {$sigHeaders['Request-Timestamp']}",
                "Signature: {$sigHeaders['Signature']}",
                "Content-Type: application/json",
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            $rawResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode >= 400 ? null : json_decode($rawResponse);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function verifySignature(Request $request): bool
    {
        try { $this->ensureConfigured(); } catch (\Throwable) { return false; }

        $clientId  = $request->header('Client-Id');
        $requestId = $request->header('Request-Id');
        $timestamp = $request->header('Request-Timestamp');
        $signature = $request->header('Signature');

        if (!$clientId || !$requestId || !$timestamp || !$signature) {
            return false;
        }

        $payload   = $request->getContent();
        $digest    = base64_encode(hash('sha256', $payload, true));

        $components = "Client-Id:{$clientId}\nRequest-Id:{$requestId}\nRequest-Timestamp:{$timestamp}\nRequest-Target:POST/v1/webhook/doku\nDigest:{$digest}";

        $expected = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $components, $this->secretKey, true)
        );

        return hash_equals($expected, $signature);
    }
}
