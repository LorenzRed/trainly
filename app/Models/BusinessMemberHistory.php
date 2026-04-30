<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessMemberHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'reservation_id',
        'user_id',
        'member_name',
        'member_email',
        'is_walk_in',
        'time_in',
        'time_out',
        'time_out_reason',
    ];

    protected $casts = [
        'is_walk_in' => 'boolean',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(BusinessReservation::class, 'reservation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
