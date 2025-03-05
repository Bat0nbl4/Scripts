<?php

namespace App\Http\Controllers\api\v1\Books;

use App\Enums\ObjectTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Models\Book;

class BookActionController extends Controller
{
    public function show($id) {
        return Book::findOrFail($id);
    }

    public function store(BookRequest $request) {
        $path = $this->service->save_image($request->file);
        $book = Book::create($request->validated() + ['image' => $path]);
        $this->logs->store_log(ObjectTypeEnum::Book, $book->id, $request->validated() + ['image' => $path]);
        return redirect(route('books.book', ['id' => $book->id]));
    }

    public function update(BookRequest $request) {
        $book = Book::find($request->id);
        $path = $this->service->save_image($request->file, $book);
        $book->update($request->validated() + ['image' => $path]);
        $this->logs->store_log(ObjectTypeEnum::Book, $book->id, $request->validated() + ['image' => $path]);
        return redirect(route('books.book', ['id' => $book->id]));
    }

    public function forceDelete($id) {
        Book::findOrFail($id)->delete();
        $this->logs->store_log(ObjectTypeEnum::Book, $id, 'DELETED');
        return redirect(route('index'));
    }
}
