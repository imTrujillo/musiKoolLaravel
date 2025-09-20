<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\CanLoadRelationships;
use App\Http\Requests\SongRequest;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @group Canciones
 *
 * Endpoints para administrar canciones.
 */
class SongController extends Controller
{
    use CanLoadRelationships;
    private array $relations = ['compasses', 'artist', 'genre', 'reviews', 'metric', 'scale'];
    /**
     * Obtener y filtrar canciones.
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Song::class);

        $title = $request->input('title');
        $filter = $request->input('filter', '');
        $genre = $request->input('genre', '');
        $artist = $request->input('artist', '');


        $songs = Song::when(
            $title,
            fn($q, $title) => $q->title($title)
        );

        $songs = match ($filter) {
            'popular' => $songs->popular(),
            'rating' => $songs->highestRated(),
            'my_songs'     => $request->user()->songs ?? $songs->whereRaw('0 = 1'),
            'by_genre' => $songs->byGenre($genre),
            'by_artist' => $songs->byArtist($artist),
            default => $songs
        };

        $songs = $this->loadRelationships($songs)->withAvgRating()->withReviewsCount()->latest();

        return SongResource::collection($songs->paginate(5));
    }

    /**
     * Mostrar una canción.
     *
     * @unauthenticated
     */
    public function show(Song $song)
    {
        Gate::authorize('view', $song);
        return new SongResource($this->loadRelationships($song));
    }

    /**
     * Guardar una canción.
     */
    public function store(SongRequest $request)
    {
        $song = Song::create($request->validated());

        $song = $this->loadRelationships($song);
        return new SongResource($song);
    }

    /**
     * Actualizar una canción.
     */
    public function update(SongRequest $request, Song $song)
    {
        Gate::authorize('update', $song);
        $song->update($request->validated());

        return new SongResource($this->loadRelationships($song));
    }

    /**
     * Eliminar un canción.
     */
    public function destroy(Song $song)
    {
        Gate::authorize('delete', $song);
        $song->delete();

        return response(status: 204);
    }
}
