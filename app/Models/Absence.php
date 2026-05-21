<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
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
