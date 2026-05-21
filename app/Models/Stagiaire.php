<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
#[Fillable(['user_id', 'groupe_id'])]
class Stagiaire extends Model
{
    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
}
