<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('auth.register', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'phone'        => 'required|string|max:20',
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($request) {
            $slug = Str::slug($request->company_name);
            $base = $slug;
            $i    = 1;
            while (Company::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $company = Company::create([
                'name'     => $request->company_name,
                'slug'     => $slug,
                'phone'    => $request->phone,
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'tax_rate' => 0,
                'is_active'=> true,
            ]);

            // Create default branch
            $branch = $company->branches()->create([
                'name'      => 'Cabang Utama',
                'address'   => $request->address ?? '',
                'is_active' => true,
            ]);

            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => $request->password,
                'company_id' => $company->id,
                'branch_id'  => $branch->id,
                'is_active'  => true,
            ]);

            $user->assignRole('owner');

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        session(['branch_id' => $user->branch_id]);

        return redirect()->route('app.dashboard')
            ->with('success', 'Selamat datang di E-Kasir! Trial 14 hari gratis sudah aktif.');
    }
}
