<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    protected $fillable = ['nom', 'filiere_id', 'annee_scolaire_id'];

    public function stagiaires()
    {
        return $this->hasMany(Stagiaire::class);
    }
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function formateurs()
    {
        return $this->belongsToMany(Formateur::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }
}
