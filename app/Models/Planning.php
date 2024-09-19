<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory;

    protected $fillable = ['medecin_id', 'datePlanning', 'heureDebut', 'heureFin'];

    // Relation avec le médecin (utilisateur)
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    // Relation avec les créneaux
    public function creneaux()
    {
        return $this->hasMany(Creneau::class);
    }
}
