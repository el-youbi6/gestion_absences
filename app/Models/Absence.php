<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'stagiaire_id',
        'module_id',
        'annee_scolaire_id',
        'date',
        'heure_debut',
        'heure_fin',
        'is_justified',
    ];

    protected $casts = [
        'is_justified' => 'boolean',
    ];

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function justification()
    {
        return $this->hasOne(Justification::class);
    }
    
    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }
}
