<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug'                     => 'starter',
                'name'                     => 'Starter',
                'description'              => 'Paket dasar untuk usaha kecil yang baru memulai.',
                'price_monthly'            => 99000,
                'price_yearly'             => 990000,
                'trial_days'               => 14,
                'max_branches'             => 1,
                'max_users'                => 3,
                'max_products'             => 50,
                'feature_qr_ordering'      => false,
                'feature_advanced_reports' => false,
                'feature_multi_device'     => false,
                'is_active'                => true,
                'sort_order'               => 1,
            ],
            [
                'slug'                     => 'pro',
                'name'                     => 'Pro',
                'description'              => 'Paket profesional dengan fitur pemesanan QR dan laporan lanjutan.',
                'price_monthly'            => 199000,
                'price_yearly'             => 1990000,
                'trial_days'               => 14,
                'max_branches'             => 3,
                'max_users'                => 10,
                'max_products'             => 200,
                'feature_qr_ordering'      => true,
                'feature_advanced_reports' => true,
                'feature_multi_device'     => false,
                'is_active'                => true,
                'sort_order'               => 2,
            ],
            [
                'slug'                     => 'enterprise',
                'name'                     => 'Enterprise',
                'description'              => 'Paket enterprise tanpa batas untuk jaringan restoran besar.',
                'price_monthly'            => 399000,
                'price_yearly'             => 3990000,
                'trial_days'               => 14,
                'max_branches'             => 999,
                'max_users'                => 999,
                'max_products'             => 9999,
                'feature_qr_ordering'      => true,
                'feature_advanced_reports' => true,
                'feature_multi_device'     => true,
                'is_active'                => true,
                'sort_order'               => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
