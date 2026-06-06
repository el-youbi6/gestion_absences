<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['nom', 'filiere_id'];

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function formateurs()
    {
        return $this->belongsToMany(Formateur::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}
