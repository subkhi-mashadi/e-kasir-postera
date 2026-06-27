<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function chargeQris(string $orderId, int $amount, string $customerName): array;
    public function getStatus(string $orderId): ?object;
    public function verifySignature(Request $request): bool;
    public function getName(): string;
}
