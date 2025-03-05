<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminActionController extends Controller
{
    public function auth(Request $request) {
        $user = User::where('login', '=', $request->login)->first();
        if (
            Hash::check($request->password, $user->password)
            and $user->user_type == UserTypeEnum::Admin->value
        ) {
            auth('admin')->attempt(['login' => $request->login, 'password' => $request->password]);
            return redirect(route('index'));
        }
        return redirect(route('admin.login'))->withErrors(['login' => 'Неверный логин или пароль']);
    }

    public function logout() {
        auth('admin')->logout();
        return redirect(route('admin.login'));
    }
}
