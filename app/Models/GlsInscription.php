<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlsInscription extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'adresse', 'niveau', 'type_cours', 'horaire_prefere', 'date_start', 'centre'];
}
