<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope('brand_representative', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->role_id == 3) { // 3 is brand_representative
                $builder->whereHas('users', function ($query) {
                    $query->where('users.id', auth()->id());
                });
            }
        });
    }

    protected $fillable = [
        'event_id',
        'name',
        'logo',
        'description',
        'stand_number'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Usuarios autorizados para acceder al portal en representación de la marca.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function loyaltyCoupons(): HasMany
    {
        return $this->hasMany(LoyaltyCoupon::class);
    }
}
