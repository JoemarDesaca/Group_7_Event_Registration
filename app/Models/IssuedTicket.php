<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedTicket extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RegistrationItem::class, 'registration_item_id');
    }
}
