<?php

namespace App\Http\Controllers\api\v1\Users;

use App\Enums\ObjectTypeEnum;
use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserActionController extends Controller
{
    public function store(UserRequest $request) {
        $data = $request->validated();
        $user = User::create($data);
        if($user) {
            auth('web')->login($user);
            unset($data['password']);
            $this->logs->store_log(ObjectTypeEnum::User, $user->id, $data, 'Регестрация');
        }
        return redirect(route('users.lk'));
    }

    public function auth(Request $request) {
        if (auth('web')->attempt(['login' => $request->login, 'password' => $request->password])) {
            return redirect(route('users.lk'));
        }
        return redirect(route('users.login'))->withErrors(['login' => 'Неверный логин или пароль']);
    }

    public function logout() {
        auth('web')->logout();
        return redirect(route('users.login'));
    }

    public function update(Request $request) {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255', Rule::unique('users', 'login')->ignore($request->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->id)],
        ]);

        $user = User::findOrFail($request->id);
        $user->update($data);

        Book::where('publisher', '=', auth()->user()->login)->update(['publisher' => $data['login']]);

        $this->logs->store_log(ObjectTypeEnum::User, $user->id, $data);

        return redirect(route('users.lk', ['comment' => 'Данные успешно изменены']));
    }

    public function update_password(Request $request) {

        $data = $request->validate(['password' => ['required', 'confirmed', 'max:255']]);
        $user = User::findOrFail($request->id);

        if (Hash::check($request->old_password, $user->password) and !Hash::check($request->password, $user->password)) {
            $user->update($data);

            $this->logs->store_log(ObjectTypeEnum::User, $user->id, ['comment' => 'Изменение пароля']);

            return redirect(route('users.lk', ['comment' => 'Пароль успешно изменеён']));
        }
        return redirect(route('users.lk'))->withErrors(['old_password' => 'Неверный пароль']);
    }

    public function forceDelete($id) {
        User::findOrFail($id)->delete();
        return redirect()->back();
    }
}
