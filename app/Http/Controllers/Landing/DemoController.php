<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    public function launch(Request $request)
    {
        $user = User::where('email', 'demo@ekasir.app')->first();

        if (! $user) {
            return redirect()->route('register')->with('info', 'Demo belum tersedia. Daftar gratis untuk mencoba.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        session(['branch_id' => $user->branch_id, 'is_demo' => true]);

        return match($request->query('redirect')) {
            'kitchen'   => redirect()->route('kitchen.index'),
            'dashboard' => redirect()->route('app.dashboard'),
            default     => redirect()->route('pos.index'),
        };
    }
}
