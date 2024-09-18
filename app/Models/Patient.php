<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','codePatient'];

    // Événement qui se déclenche avant que le modèle ne soit créé

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
