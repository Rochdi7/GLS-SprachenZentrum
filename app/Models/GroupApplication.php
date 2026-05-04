<?php

namespace App\Models;

use App\Contracts\SyncableToGoogleSheet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GroupApplication extends Model implements HasMedia, SyncableToGoogleSheet
{
    use HasFactory, InteractsWithMedia;

    /**
     * NOTE: `status` is intentionally excluded from $fillable to prevent a
     * public-form submitter from self-approving their own application via
     * mass assignment. Admin code that needs to change status must use
     * setStatus() / direct attribute assignment + save().
     */
    protected $fillable = [
        'group_id',
        'full_name',
        'whatsapp_number',
        'email',
        'note',
        'birthday',

        // Google Sheets tracking
        'google_sheet_name',
        'google_sheet_row',
        'google_sheet_synced_at',
        'google_sheet_confirmed_synced_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    /**
     * Safely set the application status from admin code. Validates against
     * the allowed status set so callers cannot inject arbitrary values.
     */
    public function setStatus(string $status): self
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid GroupApplication status: {$status}");
        }
        $this->setAttribute('status', $status);
        return $this;
    }

    protected $casts = [
        'birthday' => 'date',
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

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('card_recto')->singleFile();
        $this->addMediaCollection('card_verso')->singleFile();
    }

    // --- SyncableToGoogleSheet ---

    public function getSheetFullName(): string
    {
        return $this->full_name;
    }

    public function getSheetLevel(): string
    {
        $this->loadMissing('group');
        return $this->group?->level ?? 'N/A';
    }

    public function getSheetPhone(): string
    {
        return $this->whatsapp_number;
    }

    public function getSheetEmail(): string
    {
        return $this->email;
    }

    public function getSheetCenter(): ?string
    {
        $this->loadMissing('group.site');
        return $this->group?->site?->city;
    }

    public function getSheetGroup(): string
    {
        $groupNames = config('google-sheets.group_names', []);
        return $groupNames[$this->group_id] ?? ('Groupe ' . ($this->group_id ?? 'N/A'));
    }

    public function getSheetCentreId(): ?int
    {
        $this->loadMissing('group.site');
        return $this->group?->site?->id;
    }

    /**
     * Check if this is a consultation type (it's not - it's a group application)
     */
    public function isConsultation(): bool
    {
        return false;
    }

    /**
     * Get the address for Google Sheet (not available for group applications)
     */
    public function getSheetAdresse(): string
    {
        return '';
    }

    /**
     * Split name into first name (returns first part)
     */
    public function getSheetFirstName(): string
    {
        $parts = explode(' ', trim($this->full_name), 2);
        return $parts[0] ?? '';
    }

    /**
     * Split name into last name (returns second part)
     */
    public function getSheetLastName(): string
    {
        $parts = explode(' ', trim($this->full_name), 2);
        return $parts[1] ?? '';
    }
}
