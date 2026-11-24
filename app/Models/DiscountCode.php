<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountCode extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'minimum_order' => 'decimal:2', 'starts_at' => 'datetime', 'expires_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }
}
