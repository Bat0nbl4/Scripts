<?php

namespace App\Http\Controllers\Wallet;

use App\Enums\OwnerStatus;
use App\Enums\WalletStatus;
use App\Enums\WalletTypes;
use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WalletController extends Controller
{
    public function create() {
        $types = WalletTypes::cases();
        return view('views/wallet/create', ['types' => $types]);
    }

    public function pay_page() {
        $user_id = Auth::id();
        $wallets = Wallet::whereHas('owners', function ($query) use ($user_id) {
            $query->where('user_id', $user_id)->whereIn('status', [OwnerStatus::Creator->value, OwnerStatus::Owner->value]);
        })->get();
        return view('views/wallet/pay', ['wallets' => $wallets]);
    }

    public function edit($id) {
        $return_data = [];
        $wallet = Wallet::where('id', $id)->first();
        if (!$wallet) {
            return redirect()->back()->with(['message' => 'WALLET NOT FOUND']);
        }
        $owner = Owner::where('user_id', Auth::id())->whereIn('status', [OwnerStatus::Creator->value, OwnerStatus::Owner->value])->where('wallet_id', $wallet->id)->first();
        if (!$owner) {
            return redirect()->back()->with(['message' => 'ACCESS DENIED']);
        }
        $return_data += ['wallet' => $wallet, 'owner' => $owner];
        if ($owner->status == OwnerStatus::Creator->value) {
            $owners = Owner::where('wallet_id', $wallet->id)
                ->join('users', 'users.id', '=', 'owners.user_id')
                ->select('users.login', 'owners.id', 'owners.status')
                ->get();
            $return_data += ['owners' => $owners];
        }
        if ($wallet->type == WalletTypes::Accumulative->value and $wallet->status == WalletStatus::Lock->value) {
            $user_id = Auth::id();
            $wallets = Wallet::where('status', WalletStatus::Active->value)
                ->whereHas('owners', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->get();
            $wallets = $wallets->push($wallet);
            $timeDifference = $wallet->updated_at->diffInHours(Carbon::now());
            $return_data += ['wallets' => $wallets, 'value' => floor($timeDifference * ($wallet->value/175) * $wallet->profit_factor), 'speed' => round(($wallet->value/175) * $wallet->profit_factor, 2)];
        }
        return view('views/wallet/edit', $return_data);
    }
}
