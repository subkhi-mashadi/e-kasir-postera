<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$company = App\Models\Company::where('slug', 'demo-warung-kopi')->first();
$svc = new App\Services\DokuService($company);

echo "=== Konfigurasi ===\n";
echo "Client ID: {$company->doku_client_id}\n";
echo "Secret Key: " . substr($company->doku_secret_key ?? '', 0, 10) . "...\n";
echo "Production: " . ($company->doku_is_production ? 'yes' : 'no') . "\n\n";

echo "=== Test 1: Create Payment (Checkout API) ===\n";
try {
    $orderId = 'QR-DOKU-' . time();
    $result = $svc->chargeQris($orderId, 10000, 'Test E2E');
    echo "✅ Order ID: {$result['order_id']}\n";
    echo "✅ Redirect URL: " . ($result['redirect_url'] ?? 'TIDAK ADA') . "\n";
    echo "✅ Status: {$result['status']}\n";
} catch (Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Test 2: Check Status ===\n";
try {
    $status = $svc->getStatus($orderId ?? 'QR-DOKU-' . time());
    if ($status) {
        echo "✅ Response received\n";
        echo "Response: " . json_encode($status) . "\n";
    } else {
        echo "❌ No response\n";
    }
} catch (Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
}
