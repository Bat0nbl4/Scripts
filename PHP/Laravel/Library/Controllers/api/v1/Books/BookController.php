<?php

namespace App\Http\Controllers\api\v1\Books;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index() {
        return Book::all();
    }

    public function show($id) {
        return view('views.web.books.book', ['id' => $id]);
    }

    public function create($id = null) {
        isset($id) ? $book = Book::findOrFail($id) : $book = null;
        if ($book and (Auth::guard('admin') or Auth::user()->login == $book->publisher)) {
            return view('views.web.books.create_book')->with('book', $book);
        } else {
            return isset($book) ? abort(403) : view('views.web.books.create_book');
        }
    }
}
