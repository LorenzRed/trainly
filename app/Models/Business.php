<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $business_name
 * @property string $status
 * @property string|null $logo_path
 * @property string|null $cover_path
 * @property float $latitude
 * @property float $longitude
 * @property int|null $capacity_limit
 * @property string|null $entrance_fee
 * @property string|null $monthly_access
 * @property string|null $opening_time
 * @property string|null $closing_time
 * @property string|null $notes
 * @property string|null $rules
 * @property-read User $user
 */
class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'status',
        'logo_path',
        'cover_path',
        'latitude',
        'longitude',
        'capacity_limit',
        'entrance_fee',
        'monthly_access',
        'opening_time',
        'closing_time',
        'notes',
        'rules',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(BusinessProduct::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BusinessSubscription::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BusinessReservation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BusinessOrder::class);
    }
}
