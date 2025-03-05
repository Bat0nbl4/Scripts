<?php

namespace App\Http\Controllers\api\v1\Books\Genre;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class GenreController extends Controller
{
    public function index() {
        $genres =  Book::select('genre')->distinct()->paginate(60);

        Paginator::useBootstrap();
        return view('views.web.books.genre.genres', ['genres' => $genres ]);
    }

    public function show($genre) {
        session()->put('sort.books.search_by', 'genre');
        session()->put('sort.books.search', $genre);

        return redirect(route('index'));
    }
}
