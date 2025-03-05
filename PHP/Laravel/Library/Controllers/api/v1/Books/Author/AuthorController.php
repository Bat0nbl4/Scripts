<?php

namespace App\Http\Controllers\api\v1\Books\Author;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class AuthorController extends Controller
{
    public function index() {
        $authors =  Book::select('author')->distinct()->paginate(60);

        Paginator::useBootstrap();
        return view('views.web.books.author.authors', ['authors' => $authors ]);
    }

    public function show($author) {
        session()->put('sort.books.search_by', 'author');
        session()->put('sort.books.search', $author);

        return redirect(route('index'));
    }
}
