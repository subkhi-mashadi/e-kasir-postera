<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Company;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function make(?Company $company = null): PaymentGateway
    {
        $gateway = $company?->payment_gateway ?? 'midtrans';

        return match ($gateway) {
            'midtrans' => new MidtransService($company),
            'doku'     => new DokuService($company),
            default    => throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}"),
        };
    }
}
