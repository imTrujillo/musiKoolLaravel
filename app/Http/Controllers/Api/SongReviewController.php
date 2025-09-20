<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @group Reseñas de Canciones
 *
 * Endpoints para dejar reseñas.
 */
class SongReviewController extends Controller
{
    /**
     * Guardar una reseña
     */
    public function store(Song $song, Request $request)
    {
        $data = $request->validate([
            'rating' => 'required|min:1|max:5|integer',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $review = $song->reviews()->create($data);
        return response($review, 201);
    }

    /**
     * Actualizar una reseña
     */
    public function update(Song $song, Favorite $favorite, Request $request)
    {
        $data = $request->validate([
            'rating' => 'required|min:1|max:5|integer',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $favorite->update($data);
        $favorite->refresh();
        return response($favorite, 202);
    }
}
