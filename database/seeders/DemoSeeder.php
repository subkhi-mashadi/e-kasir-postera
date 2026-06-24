<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Superadmin ──────────────────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@ekasir.test'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active'=> true,
            ]
        );
        $superAdmin->assignRole('super_admin');

        // ── Plans ────────────────────────────────────────────────────
        $planStarter = Plan::firstOrCreate(['slug' => 'starter'], [
            'name'                    => 'Starter',
            'price_monthly'           => 99000,
            'price_yearly'            => 990000,
            'trial_days'              => 14,
            'max_branches'            => 1,
            'max_users'               => 3,
            'max_products'            => 100,
            'feature_qr_ordering'     => true,
            'feature_advanced_reports'=> false,
            'feature_multi_device'    => false,
            'is_active'               => true,
            'sort_order'              => 1,
        ]);

        Plan::firstOrCreate(['slug' => 'pro'], [
            'name'                    => 'Pro',
            'price_monthly'           => 199000,
            'price_yearly'            => 1990000,
            'trial_days'              => 14,
            'max_branches'            => 5,
            'max_users'               => 20,
            'max_products'            => 1000,
            'feature_qr_ordering'     => true,
            'feature_advanced_reports'=> true,
            'feature_multi_device'    => true,
            'is_active'               => true,
            'sort_order'              => 2,
        ]);

        Plan::firstOrCreate(['slug' => 'enterprise'], [
            'name'                    => 'Enterprise',
            'price_monthly'           => 499000,
            'price_yearly'            => 4990000,
            'trial_days'              => 14,
            'max_branches'            => -1, // unlimited
            'max_users'               => -1,
            'max_products'            => -1,
            'feature_qr_ordering'     => true,
            'feature_advanced_reports'=> true,
            'feature_multi_device'    => true,
            'is_active'               => true,
            'sort_order'              => 3,
        ]);

        // ── Demo Company ─────────────────────────────────────────────
        $company = Company::firstOrCreate(['slug' => 'warung-demo'], [
            'name'      => 'Warung Demo',
            'phone'     => '08123456789',
            'address'   => 'Jl. Demo No. 1, Jakarta',
            'currency'  => 'IDR',
            'timezone'  => 'Asia/Jakarta',
            'tax_rate'  => 0,
            'is_active' => true,
        ]);

        // Active trial subscription
        Subscription::firstOrCreate(['company_id' => $company->id], [
            'plan_id'       => $planStarter->id,
            'status'        => 'trial',
            'period'        => 'monthly',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // ── Branches ─────────────────────────────────────────────────
        $branchPusat = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'PST'],
            [
                'name'      => 'Cabang Pusat',
                'phone'     => '08111111111',
                'address'   => 'Jl. Pusat No. 1',
                'is_active' => true,
            ]
        );

        $branchCabang1 = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'CB1'],
            [
                'name'      => 'Cabang 1',
                'phone'     => '08222222222',
                'address'   => 'Jl. Cabang No. 1',
                'is_active' => true,
            ]
        );

        // ── Tables ────────────────────────────────────────────────────
        foreach (range(1, 8) as $i) {
            Table::firstOrCreate(
                ['branch_id' => $branchPusat->id, 'name' => "Meja {$i}"],
                ['capacity' => 4, 'status' => 'available', 'is_active' => true]
            );
        }

        // ── Users ─────────────────────────────────────────────────────
        $owner = User::firstOrCreate(['email' => 'owner@ekasir.test'], [
            'name'       => 'Pemilik Warung',
            'password'   => Hash::make('password'),
            'company_id' => $company->id,
            'is_active'  => true,
        ]);
        $owner->assignRole('owner');

        $manager = User::firstOrCreate(['email' => 'manager@ekasir.test'], [
            'name'       => 'Manager Pusat',
            'password'   => Hash::make('password'),
            'company_id' => $company->id,
            'branch_id'  => $branchPusat->id,
            'is_active'  => true,
        ]);
        $manager->assignRole('branch_manager');

        $kasir = User::firstOrCreate(['email' => 'kasir@ekasir.test'], [
            'name'       => 'Kasir Pusat',
            'password'   => Hash::make('password'),
            'company_id' => $company->id,
            'branch_id'  => $branchPusat->id,
            'is_active'  => true,
        ]);
        $kasir->assignRole('cashier');

        // ── Categories & Products ─────────────────────────────────────
        $catMinuman = Category::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Minuman'],
            ['sort_order' => 1, 'is_active' => true]
        );
        $catMakanan = Category::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Makanan'],
            ['sort_order' => 2, 'is_active' => true]
        );
        $catSnack = Category::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Snack'],
            ['sort_order' => 3, 'is_active' => true]
        );

        $products = [
            [$catMinuman->id, 'Es Teh Manis',    'ETM-001', 5000,  2000, 'gelas'],
            [$catMinuman->id, 'Es Jeruk',         'EJR-001', 7000,  3000, 'gelas'],
            [$catMinuman->id, 'Kopi Hitam',       'KPH-001', 8000,  3500, 'cangkir'],
            [$catMinuman->id, 'Jus Alpukat',      'JUS-001', 15000, 8000, 'gelas'],
            [$catMakanan->id, 'Nasi Goreng',      'NSG-001', 25000, 12000,'porsi'],
            [$catMakanan->id, 'Mie Goreng',       'MEG-001', 22000, 10000,'porsi'],
            [$catMakanan->id, 'Nasi Uduk',        'NSU-001', 20000, 9000, 'porsi'],
            [$catMakanan->id, 'Ayam Bakar',       'AYB-001', 35000, 18000,'porsi'],
            [$catSnack->id,   'Kentang Goreng',   'KTG-001', 18000, 8000, 'porsi'],
            [$catSnack->id,   'Pisang Goreng',    'PSG-001', 12000, 5000, 'porsi'],
        ];

        $branches = [$branchPusat, $branchCabang1];

        foreach ($products as [$catId, $name, $sku, $price, $cost, $unit]) {
            $product = Product::firstOrCreate(
                ['company_id' => $company->id, 'sku' => $sku],
                [
                    'category_id' => $catId,
                    'name'        => $name,
                    'unit'        => $unit,
                    'price'       => $price,
                    'cost_price'  => $cost,
                    'tax_rate'    => 0,
                    'track_stock' => true,
                    'is_active'   => true,
                ]
            );

            foreach ($branches as $branch) {
                Inventory::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $branch->id],
                    ['qty' => 100, 'min_qty' => 10]
                );
            }
        }
    }
}
