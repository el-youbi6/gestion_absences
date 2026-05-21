<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id'])]
class Formateur extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groupes()
    {
        return $this->belongsToMany(Groupe::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }
}
