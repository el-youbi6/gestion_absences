<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }
}
