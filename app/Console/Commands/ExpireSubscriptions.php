<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:expire')]
#[Description('Mark overdue subscriptions as expired')]
class ExpireSubscriptions extends Command
{
    public function handle(): void
    {
        $count = (new SubscriptionService())->checkAndExpire();
        $this->info("Expired {$count} subscription(s).");
    }
}
