<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserActionController extends Controller
{
    public function store(UserRequest $request) {
        $data = $request->validated();

        User::create($data);
        $credentials = $request->only('login', 'password');
        if (Auth::attempt($credentials)) {
            return redirect(route('user.lk'));
        }
        return back()->withInput($request->input());
    }

    public function auth(Request $request) {
        $credentials = $request->only('login', 'password');
        if (Auth::attempt($credentials)) {
            return redirect(route('user.lk'));
        }
        return back()->withInput($request->input());
    }

    public function logout() {
        Auth::logout();
        session()->flush();
        return redirect(route('user.login'));
    }

    public function get_session() {
        return response()->json(Auth::user());
    }
}
