<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'book_id'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function countFollowers($book_id){
        return $this->where("book_id", $book_id)->count();
    }
}
