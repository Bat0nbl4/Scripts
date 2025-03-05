<?php

namespace App\Http\Controllers\api\v1;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SortController extends Controller
{
    public function ChangeSortSettings(Request $request) {
        if ($request->panel == 'users') {
            session()->put([
                'sort.users.sort_by' => $request->sort_by,
                'sort.users.reverse' => $request->reverse,
                'sort.users.search_by' => $request->search_by,
                'sort.users.search' => $request->search,
            ]);
        }
        if ($request->panel == 'books') {
            session()->put([
                'sort.books.sort_by' => $request->sort_by,
                'sort.books.reverse' => $request->reverse,
                'sort.books.search_by' => $request->search_by,
                'sort.books.search' => $request->search,
            ]);
        }
        if ($request->panel == 'logs') {
            session()->put([
                'sort.logs.sort_by' => $request->sort_by,
                'sort.logs.reverse' => $request->reverse,
                'sort.logs.search_by' => $request->search_by,
                'sort.logs.search' => $request->search,
            ]);
        }
        return redirect()->back();
    }

    public static function reverseCheck($reverse) {
        if (!$reverse) {
            return 'asc';
        } else {
            return $reverse;
        }
    }

    public static function UserSort() {
        session()->put('sort.users.reverse', self::reverseCheck(session()->get('sort.users.reverse')));
        if (session()->get('sort.users.search')) {
            return User::where(
                    session()->get('sort.users.search_by'),
                    session()->get('sort.users.search')
                )
                ->orderBy(
                    session()->get('sort.users.sort_by'),
                    session()->get('sort.users.reverse')
                )
                ->paginate(50);
        } else {
            return User::orderBy(
                    session()->get('sort.users.sort_by'),
                    session()->get('sort.users.reverse')
                )
                ->paginate(50);
        }
    }

    public static function BookSort() {
        session()->put('sort.books.reverse', self::reverseCheck(session()->get('sort.books.reverse')));
        if (session()->get('sort.books.search')) {
            return Book::where(
                    session()->get('sort.books.search_by'),
                    session()->get('sort.books.search')
                )
                ->orderBy(
                    session()->get('sort.books.sort_by'),
                    session()->get('sort.books.reverse')
                )
                ->paginate(50);
        } else {
            return Book::orderBy(session()->get('sort.books.sort_by'), session()->get('sort.books.reverse'))->paginate(50);
        }
    }

    public static function PersonalBookSort($person) {
        session()->put('sort.books.reverse', self::reverseCheck(session()->get('sort.books.reverse')));
        if (session()->get('sort.books.search')) {
            return Book::where('publisher', $person)
                ->where(
                    session()->get('sort.books.search_by'),
                    session()->get('sort.books.search')
                )
                ->orderBy(
                    session()->get('sort.books.sort_by'),
                    session()->get('sort.books.reverse')
                )
                ->paginate(50);
        } else {
            return Book::where('publisher', $person)
                ->orderBy(
                    session()->get('sort.books.sort_by'),
                    session()->get('sort.books.reverse')
                )
                ->paginate(50);
        }
    }

    public static function LogSort() {
        session()->put('sort.logs.reverse', self::reverseCheck(session()->get('sort.logs.reverse')));
        if (session()->get('sort.logs.search')) {
            return Log::where(
                    session()->get('sort.logs.search_by'),
                    session()->get('sort.logs.search')
                )
                ->orderBy(
                    session()->get('sort.logs.sort_by'),
                    session()->get('sort.logs.reverse')
                )
                ->paginate(50);
        } else {
            return Log::orderBy(
                    session()->get('sort.logs.sort_by'),
                    session()->get('sort.logs.reverse')
                )
                ->paginate(50);
        }
    }
}
