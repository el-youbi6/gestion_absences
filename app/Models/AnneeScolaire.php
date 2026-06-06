<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnneeScolaire extends Model
{
    protected $fillable = ['libelle'];

    public function groupes()
    {
        return $this->hasMany(Groupe::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}
