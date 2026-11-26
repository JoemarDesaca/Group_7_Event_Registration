<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'registration_opens_at' => 'datetime', 'registration_closes_at' => 'datetime'];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(AddOn::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function discountCodes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    public function isAcceptingRegistrations(): bool
    {
        return $this->status === 'published'
            && $this->starts_at->isFuture()
            && (! $this->registration_opens_at || $this->registration_opens_at->isPast())
            && ($this->registration_closes_at ? $this->registration_closes_at->isFuture() : $this->starts_at->isFuture());
    }
}
