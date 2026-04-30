<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $business_id
 * @property string $duration
 * @property string $subscription_type
 * @property string|float $price
 * @property string|null $expires_at
 * @property-read Business $business
 */
class BusinessSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'duration',
        'subscription_type',
        'price',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BusinessOrder::class, 'subscription_id');
    }
}
