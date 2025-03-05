<?php

namespace App\Http\Controllers\api\v1\Users;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\api\v1\SortController;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index($id) {
        $user = User::findOrFail($id);
        if ($user->user_type == UserTypeEnum::Publisher->value) {
            $books = SortController::PersonalBookSort($user->login);
        } else {
            $books = null;
        }
        return view('views/web/users/user', ['user' => $user, 'books' => $books]);
    }

    public function create() {
        return view('views/web/users/create_user');
    }

    public function login() {
        return view('views/web/users/login');
    }

    public function lk($comment = null) {
        $user = auth()->user();
        $books = SortController::PersonalBookSort($user->login);
        return view('views/web/users/lk', ['user' => $user, 'books' => $books, 'comment' => $comment]);
    }
}
