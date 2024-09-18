<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialite extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'description'];

// Relation avec le modèle Medecin
    public function medecins()
    {
        return $this->hasMany(Medecin::class, 'specialite_id');
    }
}
