<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $business_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $reserved_at
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property string|null $payment_method
 * @property-read Business $business
 * @property-read User $user
 */
class BusinessReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'reserved_at',
        'accepted_at',
        'payment_method',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
