<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RhythmicFigure extends Model
{
    /** @use HasFactory<\Database\Factories\RhythmicFigureFactory> */
    use HasFactory;
    public $timestamps = false;

    public static array $rhythmic_figures = ['Redonda', 'Blanca', 'Negra', 'Corchea'];

    public function musicalNotes(): HasMany
    {
        return $this->hasMany(MusicalNote::class);
    }
}
