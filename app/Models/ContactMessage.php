<?php

namespace App\Models;

use App\Enums\ContactStatus;
use App\Enums\ContactSubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// R-CONT-01→07 : demandes de renseignements (jamais des commandes)
class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message',
        'privacy_accepted_at', 'status', 'handled_by', 'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'subject' => ContactSubject::class,
            'status' => ContactStatus::class,
            'privacy_accepted_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeUnread(Builder $query): void
    {
        $query->where('status', ContactStatus::New->value);
    }
}
