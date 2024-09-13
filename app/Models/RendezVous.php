<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use HasFactory;
    protected $fillable = ['patient_id', 'creneau_id', 'status', 'motif'];

    // Relation avec le patient (utilisateur)
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // Relation avec le créneau
    public function creneau()
    {
        return $this->belongsTo(Creneau::class);
    }
}
