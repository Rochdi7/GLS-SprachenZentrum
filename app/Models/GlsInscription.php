<?php

namespace App\Models;

use App\Contracts\SyncableToGoogleSheet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlsInscription extends Model implements SyncableToGoogleSheet
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'phone',
        'adresse',
        'niveau',
        'type_cours',
        'horaire_prefere',
        'date_start',
        'centre',
        'group_id',
        'form_source',

        // Google Sheets tracking
        'google_sheet_name',
        'google_sheet_row',
        'google_sheet_synced_at',
        'google_sheet_confirmed_synced_at',
    ];

    protected $casts = [
        'date_start' => 'date',
        'google_sheet_synced_at' => 'datetime',
        'google_sheet_confirmed_synced_at' => 'datetime',
    ];

    public function isSyncedToSheet(): bool
    {
        return $this->google_sheet_synced_at !== null;
    }

    public function isSyncedToConfirmedSheet(): bool
    {
        return $this->google_sheet_confirmed_synced_at !== null;
    }

    /**
     * centre stores the site ID.
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'centre');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // --- SyncableToGoogleSheet ---

    public function getSheetFullName(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    public function getSheetLevel(): string
    {
        return $this->niveau ?? 'N/A';
    }

    public function getSheetPhone(): string
    {
        return $this->phone;
    }

    public function getSheetEmail(): string
    {
        return $this->email;
    }

    public function getSheetCenter(): ?string
    {
        if ($this->type_cours === 'en_ligne') {
            return 'Online';
        }

        return $this->site?->city;
    }

    public function getSheetGroup(): string
    {
        $this->loadMissing('group');

        if ($this->group?->time_range) {
            return 'Groupe ' . $this->group->time_range;
        }

        $groupNames = config('google-sheets.group_names', []);
        return $groupNames[$this->group_id] ?? ('Groupe ' . ($this->group_id ?? 'N/A'));
    }

    public function getSheetCentreId(): ?int
    {
        if ($this->type_cours === 'en_ligne') {
            return 0; // maps to ONLINE
        }

        return $this->centre ? (int) $this->centre : null;
    }

    /**
     * Check if this is a consultation type (it's not - it's an inscription)
     */
    public function isConsultation(): bool
    {
        return false;
    }

    /**
     * Get the address for Google Sheet
     */
    public function getSheetAdresse(): string
    {
        return $this->adresse ?? '';
    }

    /**
     * Get first name (nom) for Google Sheet
     */
    public function getSheetFirstName(): string
    {
        return $this->nom ?? '';
    }

    /**
     * Get last name (prenom) for Google Sheet
     */
    public function getSheetLastName(): string
    {
        return $this->prenom ?? '';
    }
}
