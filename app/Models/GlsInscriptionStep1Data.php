<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlsInscriptionStep1Data extends Model
{
    protected $table = 'gls_inscription_step1_data';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'phone',
        'adresse',
        'form_source',
        'ip_address',
        'user_agent',
    ];
}
