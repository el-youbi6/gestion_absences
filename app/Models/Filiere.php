<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    protected $fillable = ['nom'];

    public function groupes()
    {
        return $this->hasMany(Groupe::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
