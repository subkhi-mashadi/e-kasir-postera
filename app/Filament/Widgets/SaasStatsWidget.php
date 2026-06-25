<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaasStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeSubs  = Subscription::where('status', 'active')->count();
        $trialSubs   = Subscription::where('status', 'trial')
                           ->where('trial_ends_at', '>', now())->count();
        $expiredSubs = Subscription::where('status', 'expired')->count();
        $mrr         = SubscriptionInvoice::where('status', 'paid')
                           ->whereMonth('paid_at', now()->month)
                           ->whereYear('paid_at', now()->year)
                           ->sum('amount');

        return [
            Stat::make('Tenant Aktif', $activeSubs)
                ->description('Berlangganan aktif')
                ->icon('heroicon-o-building-storefront')
                ->color('success'),
            Stat::make('Trial', $trialSubs)
                ->description('Masa percobaan')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('MRR Bulan Ini', 'Rp ' . number_format($mrr, 0, ',', '.'))
                ->description('Pendapatan bulan ini')
                ->icon('heroicon-o-banknotes')
                ->color('primary'),
            Stat::make('Berakhir', $expiredSubs)
                ->description('Langganan kedaluwarsa')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
