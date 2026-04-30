<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $business_id
 * @property string $name
 * @property string|null $description
 * @property string $image_path
 * @property string|float $price
 * @property bool $is_sold_out
 * @property-read Business $business
 */
class BusinessProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'image_path',
        'price',
        'is_sold_out',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_sold_out' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BusinessOrder::class, 'product_id');
    }
}
