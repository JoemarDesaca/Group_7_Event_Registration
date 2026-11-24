<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registrationItems(): HasMany
    {
        return $this->hasMany(RegistrationItem::class);
    }
}
