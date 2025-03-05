<?php

namespace App\Models;

use App\Enums\BookEditionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'genre',
        'description',
        'publisher',
        'edition',
        'image',
    ];

    protected $casts = [
        'edition' => BookEditionEnum::class
    ];
}
