<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChordResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\SongResource;
use App\Http\Resources\UserResource;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @group Favoritos
 *
 * Endpoints para marcar o desmarcar un item como favorito.
 */
class FavoriteController extends Controller
{
    /**
     * Buscar favoritos de un modelo.
     */
    public function index(User $user, Request $request)
    {
        Gate::authorize('viewMySongs', $user);
        $model = $request->input('model', 'Song');

        $items = Favorite::myFavorites($model, $request->user());

        return match ($model) {
            "Song" => SongResource::collection($items->withAvgRating()->withReviewsCount()->latest()->paginate(5)),
            "Chord" => ChordResource::collection($items->paginate(5)),
            "User" => UserResource::collection($items->withSongsCount()->paginate(5)),
            default => ""
        };
    }

    /**
     * Marcar un item como favoritos.
     */
    public function store(User $user, Request $request)
    {
        $data = $request->validate([
            'model' => 'required|in:Song,Chord,User',
            'favoritable_id' => 'required|integer|min:1'
        ]);

        Favorite::validateFavoriteItem($user, $data['model'], $data['favoritable_id']);

        $favorite = $user->favorites()->create($data);

        return new FavoriteResource($favorite);
    }

    /**
     * Desmarcar un item de favoritos.
     */
    public function destroy(User $user, Favorite $favorite)
    {
        Gate::authorize('delete', $user);
        $favorite->delete();

        return response(status: 204);
    }
}
