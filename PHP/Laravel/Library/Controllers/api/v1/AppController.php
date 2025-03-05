<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class AppController extends Controller
{
    public static function CheckSortHas() {
        if (!session()->has('sort')) {
            session()->put([ 'sort' => [
                'users' => ['sort_by' => 'id', 'reverse' => 'asc', 'search_by' => 'id', 'search' => null],
                'books' => ['sort_by' => 'id', 'reverse' => 'asc', 'search_by' => 'id', 'search' => null],
                'logs' => ['sort_by' => 'id', 'reverse' => 'asc', 'search_by' => 'id', 'search' => null],
            ]]);
        }
    }

    public function index() {
        self::CheckSortHas();

        $books = SortController::BookSort();
        $genres = Book::distinct()->orderBy('genre')->get(['genre']);

        Paginator::useBootstrap();
        return view('views.web.index', ['books' => $books, 'genres' => $genres]);
    }
}
