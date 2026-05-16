<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormResult extends Model
{
    protected $fillable = [
        'form_id',
        'data',
        'ip_address',
    ];

    // NOTE: Do NOT cast 'data' as 'array' - Voyager's browse view uses
    // htmlspecialchars() which fails on arrays. Data is stored as a JSON string.
    // Use getDataArrayAttribute() for array access in controllers.
    protected $casts = [];

    public function form()
    {
        return $this->belongsTo(CustomForm::class, 'form_id');
    }

    /**
     * Get data as PHP array (decoded from JSON string).
     */
    public function getDataArrayAttribute(): array
    {
        return json_decode($this->data, true) ?? [];
    }

    /**
     * Get the associated event name.
     */
    public function getEventNameAttribute()
    {
        return $this->form->event->name ?? 'N/A';
    }

    /**
     * Get the associated landing page name.
     */
    public function getLandingPageNameAttribute()
    {
        return $this->form->landingPage->name ?? 'N/A';
    }
}
