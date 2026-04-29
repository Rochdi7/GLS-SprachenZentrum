<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WeeklyReport extends Model
{
    protected $fillable = [
        'teacher_id',
        'group_id',
        'skill',
        'report_date',
        'notes',
        'attachment_path',
        'attachment_original_name',
        'created_by',
    ];

    public const SKILLS = [
        'lesen'      => 'Lesen',
        'hoeren'     => 'Hören',
        'grammatik'  => 'Grammatik',
        'schreiben'  => 'Schreiben',
        'sprechen'   => 'Sprechen',
        'aktivitaet' => 'Activités',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->hasMany(WeeklyReportAttachment::class, 'weekly_report_id')
            ->orderBy('created_at');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? Storage::disk('public')->url($this->attachment_path)
            : null;
    }
}
