<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity_total',
        'quantity_available',
        'sales_start',
        'sales_end',
        'is_active',
        'special_conditions'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sales_start' => 'datetime',
        'sales_end' => 'datetime',
        'is_active' => 'boolean',
        'special_conditions' => 'array'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
