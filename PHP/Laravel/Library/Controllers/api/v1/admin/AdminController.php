<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\api\v1\AppController;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\api\v1\SortController;

class AdminController extends Controller
{

    public function login() {
        return view('views.web.admin.login');
    }

    // Панели
    public function show_users_panel() {

        AppController::CheckSortHas();

        $users = SortController::UserSort();

        Paginator::useBootstrap();
        return view('views.web.admin.panel.users_panel', ['users' => $users]);
    }

    public function show_books_panel() {

        AppController::CheckSortHas();

        $books = SortController::BookSort();

        Paginator::useBootstrap();
        return view('views.web.admin.panel.books_panel', ['books' => $books]);
    }

    public function show_logs_panel() {

        AppController::CheckSortHas();

        $logs = SortController::LogSort();

        Paginator::useBootstrap();
        return view('views.web.admin.panel.logs_panel', ['logs' => $logs]);
    }
}
