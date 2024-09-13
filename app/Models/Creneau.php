<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creneau extends Model
{
    use HasFactory;
    protected $fillable = ['planning_id', 'heureDebut', 'heureFin'];

    // Relation avec le planning
    public function planning()
    {
        return $this->belongsTo(Planning::class);
    }

    // Relation avec les rendez-vous
    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class);
    }
}
