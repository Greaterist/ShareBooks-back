<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Favourite;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavouriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $favs = Favourite::all();
        return response()->json($favs);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
        ]);

        Favourite::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
        ]);

        return response()->json(['message' => 'Favourite stored successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show($user_id)
    {

        //$favs = Favourite::where('user_id', $user_id)->get();
        $books = Book::withCount('favourites');
        $favs = Favourite::joinSub($books, 'books', function ($join) {
            $join->on('favourites.book_id', '=', 'books.id');
        })
            ->select('favourites.*', 'books.name', 'books.author', 'books.img', 'books.description', 'books.favourites_count')
            ->where('user_id', $user_id)
            ->get();


        return response()->json($favs);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Favourite $favourite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Favourite $favourite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Favourite $favourite)
    {
        try {
            $favourite->delete();
            return response()->json('ok');
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            return response()->json('error', '400');
        }
    }

    public function getBooksByUser(Request $request, $userId)
    {

        $books = Favourite::where('user_id', $userId)->with('book')->get();

        return response()->json(['books' => $books]);
    }

    public function getRecommendationsForId($id)
    {
        
        $currentUserId = $id;
        $books = Book::withCount('favourites');
        $current_user = DB::table('favourites')
            ->where('favourites.user_id', $currentUserId);

        $top_users = DB::table('favourites')
            ->joinSub($current_user, 'current_user', function ($join) {
                $join->on('favourites.book_id', '=', 'current_user.book_id');
            })
            ->selectRaw('favourites.user_id, COUNT(favourites.book_id) as fav_count')
            ->where('favourites.user_id', '!=', $currentUserId)
            ->groupBy('favourites.user_id')
            ->orderByRaw('fav_count')
            ->limit(10);

        $result = DB::table('favourites')
            ->joinSub($top_users, 'top_users', function ($join) {
                $join->on('favourites.user_id', '=', 'top_users.user_id');
            })
            ->joinSub($books, 'books', function ($join) {
                $join->on('favourites.book_id', '=', 'books.id');
            })
            ->whereNotIn('favourites.book_id', function ($query)  use ($currentUserId) {
                $query->select('book_id')->from('favourites')->where('user_id', $currentUserId);
            })
            ->select('books.id', 'books.name', 'books.author', 'books.img', 'books.description', 'books.favourites_count')
            ->groupBy('top_users.fav_count', 'books.id', 'books.name', 'books.author', 'books.img', 'books.description', 'books.favourites_count')
            ->orderByDesc('top_users.fav_count')
            ->get()
            ->unique();

        return response()->json($result);
    }
}
