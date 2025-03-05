<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login() {
        return view('views/user/login');
    }

    public function create() {
        return view('views/user/create');
    }

    public function lk() {
        $user_id = Auth::id();
        $wallets = Wallet::whereHas('owners', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->get();
        return view('views/user/lk', ['wallets' => $wallets]);
    }
}
