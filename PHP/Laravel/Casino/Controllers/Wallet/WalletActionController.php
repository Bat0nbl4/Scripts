<?php

namespace App\Http\Controllers\Wallet;

use App\Enums\OwnerStatus;
use App\Enums\WalletStatus;
use App\Enums\WalletTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalletRequest;
use App\Models\Owner;
use App\Models\Wallet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletActionController extends Controller
{
    public function store(WalletRequest $request) {
        $validated = $request->validated();
        $user = User::find(Auth::id());
        if ($user->freeWallet == true) {
            $wallet = Wallet::create($validated);
            Owner::create([
                'user_id' => Auth::id(),
                'status' => OwnerStatus::Creator->value,
                'wallet_id' => $wallet->id
            ]);
            $wallet->update(['value' => 2500]);
            $user->update(['freeWallet' => false]);
        } else {
            $active_wallet = Wallet::where('id', session()->get('wallet.active'))->first();
            if (!$active_wallet) {
                return redirect()->route('wallet.create')->with('message', 'ACTIVE WALLET NOT FOUND')->withInput();
            }
            if ($active_wallet->value < 50000) {
                return redirect()->back()->withErrors(['from_wallet' => 'INSUFFICIENT FUNDS'])->withInput();
            }
            $active_wallet->value -= 50000;
            $active_wallet->save();
            $wallet = Wallet::create($validated);
            Owner::create([
                'user_id' => Auth::id(),
                'status' => OwnerStatus::Creator->value,
                'wallet_id' => $wallet->id
            ]);
        }
        return redirect(route('user.lk'));
    }

    public function forceDelete($id) {
        $wallet = Wallet::find($id);
        if (!$wallet) {
            return redirect()->route('user.lk')->with('message', 'WALLET NOT FOUND');
        }
        if (!Owner::where('user_id', Auth::id())->where('status', OwnerStatus::Creator->value)->where('wallet_id', $id)->exists()) {
            return redirect()->route('user.lk')->with('message', 'ACCESS DENIED');
        }
        $wallet->delete();
        return redirect()->route('user.lk')->with(['message' => 'WALLET '.$wallet->name.' WAS DELETED']);
    }

    public function add_owner(Request $request) {
        $user = User::where('login', $request->user_login)->first();
        $wallet = Wallet::find($request->wallet_id);
        if (!$user) {
            return redirect()->back()->withErrors(['user_name' => 'USER NOT FOUND']);
        }
        if (!$wallet) {
            return redirect()->back()->withErrors('user_name', 'WALLET NOT FOUND');
        }
        if (!Owner::where('user_id', Auth::id())->whereIn('status', [OwnerStatus::Creator->value, OwnerStatus::Owner->value])->where('wallet_id', $wallet->id)->exists()) {
            return redirect()->back()->withErrors('user_name', 'ACCESS DENIED');
        }
        if (Owner::where('user_id', $user->id)->where('wallet_id', $wallet->id)->exists()) {
            return redirect()->back()->withErrors(['user_name' => 'THIS USER HAS ALREADY BEEN ADDED']);
        }
        Owner::create([
            'user_id' => $user->id,
            'status' => OwnerStatus::User->value,
            'wallet_id' => $wallet->id
        ]);
        return redirect()->back()->with('message', 'THIS USER HAS BEEN SUCCESSFULLY ADDED');
    }

    public function remove_owner($id) {
        $owner = Owner::where('id', $id)->first();
        if (!$owner) {
            return redirect()->back()->with('message', 'OWNER NOT FOUND');
        }
        if ($owner->user_id == Auth::id() or Owner::where('user_id', Auth::id())->where('status', OwnerStatus::Creator->value)->exists()) {
            $owner->delete();
            return redirect()->route('user.lk')->with('message', 'THIS USER HAS BEEN SUCCESSFULLY REMOVED');
        }
        return redirect()->back()->with('message', 'ACCESS DENIED');
    }

    public function change_owner_status(Request $request) {
        $owner = Owner::where('id', $request->id)->first();
        if (!$owner) {
            return redirect()->back()->withErrors('user_name', 'OWNER NOT FOUND');
        }
        if (!Owner::where('user_id', Auth::id())->where('status', [OwnerStatus::Creator->value, OwnerStatus::Owner->value])->where('wallet_id', $owner->wallet_id)->exists()) {
            return redirect()->back()->withErrors('user_name', 'ACCESS DENIED');
        }
        if (!in_array($request->status, array_column(OwnerStatus::cases(), 'value'), true)) {
            return redirect()->back()->withErrors('user_name', 'UNKNOWN STATUS');
        }
        $owner->status = OwnerStatus::Owner->value;
        $owner->save();
        return redirect()->back()->withErrors('user_name', 'OPERATION WAS COMPLETED SUCCESSFULLY');
    }

    public function set_active($id) {
        $wallet = Wallet::find($id);
        if (!$wallet) {
            return redirect()->route('user.lk')->with('message', 'WALLET NOT FOUND');
        }
        if ($wallet->status != WalletStatus::Active->value) {
            return redirect()->back()->with(['message' => 'IMPOSSIBLE TO PERFORM A WALLET OPERATION']);
        }
        if ($wallet->type != WalletTypes::Storage->value) {
            return redirect()->route('user.lk')->with('message', 'THIS WALLET CANNOT BE ACTIVATED');
        }
        if (!Owner::where('user_id', Auth::id())->where('wallet_id', $wallet->id)->exists()) {
            return redirect()->route('user.lk')->with('message', 'ACCESS DENIED');
        }
        session()->put('wallet', ['active' => $id]);
        return redirect()->back()->with('message', 'OPERATION WAS COMPLETED SUCCESSFULLY');
    }

    public function pay(Request $request) {
        if ($request->pay_value < 1) {
            return redirect()->back()->withErrors(['pay_value' => 'THE TRANSFER AMOUNT CANNOT BE LESS THAN 1'])->withInput();
        }
        $wallet1 = Wallet::where('id', $request->from_wallet)->first();
        if (!$wallet1) {
            return redirect()->back()->withErrors(['from_wallet' => 'WALLET NOT FOUND'])->withInput();
        }
        if ($wallet1->status != WalletStatus::Active->value) {
            return redirect()->back()->withErrors(['from_wallet' => 'IMPOSSIBLE TO PERFORM A WALLET OPERATION'])->withInput();
        }
        if (!Owner::where('user_id', Auth::id())->whereIn('status', [OwnerStatus::Creator->value, OwnerStatus::Owner->value])->where('wallet_id', $wallet1->id)->exists()) {
            return redirect()->back()->withErrors(['from_wallet' => 'ACCESS DENIED'])->withInput();
        }
        if ($wallet1->value < $request->pay_value) {
            return redirect()->back()->withErrors(['from_wallet' => 'INSUFFICIENT FUNDS'])->withInput();
        }
        $wallet2 = Wallet::where('name', $request->to_wallet)->first();
        if (!$wallet2) {
            return redirect()->back()->withErrors(['to_wallet' => 'WALLET NOT FOUND'])->withInput();
        }
        if ($wallet2->status != WalletStatus::Active->value) {
            return redirect()->back()->withErrors(['to_wallet' => 'THE WALLET CANNOT ACCEPT FUNDS'])->withInput();
        }
        $wallet1->value -= $request->pay_value;
        $wallet2->value += $request->pay_value;
        $wallet1->save();
        $wallet2->save();
        return redirect()->back()->with(['success' => 'OPERATION WAS COMPLETED SUCCESSFULLY'])->withInput();
    }

    public function LockWallet($id) {
        $wallet = Wallet::find($id);
        if (!$wallet) {
            return redirect()->back()->with('message', 'WALLET NOT FOUND');
        }
        if ($wallet->type == WalletStatus::Blocked->value) {
            return redirect()->back()->with('message', 'THIS WALLET IS BLOCKED');
        }
        if (!Owner::where('user_id', Auth::id())->where('wallet_id', $wallet->id)->exists()) {
            return redirect()->back()->with('message', 'ACCESS DENIED');
        }
        if (session()->get('wallet.active') == $wallet->id) {
            session()->put('wallet.active', null);
        }
        $wallet->update(['status' => WalletStatus::Lock->value]);
        return redirect()->back()->with('message', 'OPERATION WAS COMPLETED SUCCESSFULLY');
    }

    public function UnlockWallet($id) {
        $wallet = Wallet::find($id);
        if (!$wallet) {
            return redirect()->back()->with('message', 'WALLET NOT FOUND');
        }
        if ($wallet->type == WalletStatus::Blocked->value) {
            return redirect()->back()->with('message', 'THIS WALLET IS BLOCKED');
        }
        if (!Owner::where('user_id', Auth::id())->where('wallet_id', $wallet->id)->exists()) {
            return redirect()->back()->with('message', 'ACCESS DENIED');
        }
        $wallet->update(['status' => WalletStatus::Active->value]);
        return redirect()->back()->with('message', 'OPERATION WAS COMPLETED SUCCESSFULLY');
    }

    public function Withdraw(Request $request) {
        $wallet = Wallet::find($request->wallet_from);
        if (!$wallet) {
            return redirect()->back()->with('message', 'WALLET [1] NOT FOUND');
        }
        if ($wallet->type != WalletTypes::Accumulative->value) {
            return redirect()->back()->with('message', 'THIS WALLET IS NOT ACCUMULATIVE');
        }
        if ($wallet->status == WalletStatus::Blocked->value) {
            return redirect()->back()->with('message', 'THIS WALLET IS BLOCKED');
        }
        if (!Owner::where('user_id', Auth::id())->whereIn('status', [OwnerStatus::Creator->value, OwnerStatus::Owner->value])->where('wallet_id', $wallet->id)->exists()) {
            return redirect()->back()->with('message', 'ACCESS DENIED');
        }
        $wallet_to = Wallet::find($request->wallet_to);
        if (!$wallet_to) {
            return redirect()->back()->with('message', 'WALLET [2] NOT FOUND');
        }
        if ($wallet->id != $wallet_to->id and $wallet_to->status != WalletStatus::Active->value) {
            return redirect()->back()->with('message', 'THIS WALLET IS BLOCKED OR LOCKED');
        }
        if (!Owner::where('user_id', Auth::id())->whereIn('status', [OwnerStatus::Creator->value, OwnerStatus::Owner->value])->where('wallet_id', $wallet_to->id)->exists()) {
            return redirect()->back()->with('message', 'ACCESS DENIED');
        }
        $timeDifference = $wallet->updated_at->diffInHours(Carbon::now());
        $wallet_to->value += floor($timeDifference * ($wallet->value/175) * $wallet->profit_factor);
        $wallet_to->save();
        $wallet->updated_at = Carbon::now();
        $wallet->save();
        return redirect()->back()->with('message', 'OPERATION WAS COMPLETED SUCCESSFULLY');
    }
}
