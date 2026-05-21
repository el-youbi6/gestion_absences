<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justification extends Model
{
    public function absence()
    {
        return $this->belongsTo(Absence::class);
    }
}
