<?php

namespace App\Rules;

use App\Models\Song;
use App\Models\Compass;
use App\Models\RhythmicFigure;
use App\Models\MusicalNote;
use Illuminate\Contracts\Validation\Rule;

class CompassMetric implements Rule
{
    protected ?Song $song = null;
    protected ?Compass $compass = null;
    protected ?MusicalNote $musical_note = null;

    public function __construct(
        protected mixed $rhythmic_figure_id,
        protected mixed $is_dotted,
        ?MusicalNote $musical_note = null
    ) {
        $this->musical_note = $musical_note;
    }

    public function passes($attribute, $value): bool
    {
        // Obtenemos Song y Compass de la ruta actual
        $this->song = request()->route('song');
        $this->compass = request()->route('compass');

        // Si no existen (por ejemplo en Scribe), dejamos pasar
        if (!$this->song || !$this->compass) {
            return true;
        }

        // Convertir de forma segura
        $figureId = is_numeric($this->rhythmic_figure_id) ? (int) $this->rhythmic_figure_id : null;
        $isDotted = filter_var($this->is_dotted, FILTER_VALIDATE_BOOLEAN);

        if (!$figureId) {
            return false;
        }

        $figure = RhythmicFigure::find($figureId);
        if (!$figure) {
            return false;
        }

        $compassSize = $this->compass->beats;
        $figureDuration = $figure->duration * ($isDotted ? 1.5 : 1);

        return $figureDuration <= $compassSize;
    }

    public function message(): string
    {
        return 'La figura rítmica no encaja con la métrica del compás.';
    }
}
