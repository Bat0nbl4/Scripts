<?php

namespace App\Http\Controllers\Hack;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class HackController extends Controller
{
    public function menu() {
        $wallet = Wallet::where('id', session()->get('wallet.active'))->first();
        return view('views/hack/menu', ['wallet' => $wallet]);
    }

    public function hack() {
        $text = session()->get('hack.text');
        return view('views/hack/hack', ['text' => $text]);
    }
}
