<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['libelle'])]
class AnneeScolaire extends Model
{
    public function groupes()
    {
        return $this->hasMany(Groupe::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}
