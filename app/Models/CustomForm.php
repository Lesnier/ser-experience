<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomForm extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'event_id',
        'landing_page_id',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID is now generated in the saving hook to ensure it's available for the snippet
        });

        static::saving(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            $fields = is_array($model->fields) ? $model->fields : json_decode($model->fields, true);
            if (!is_array($fields)) {
                $fields = [];
            }
            
            $html = "<!-- Código listo para copiar y pegar en tu Landing Page -->\n";
            $html .= "<form id=\"custom-form-{$model->uuid}\">\n";
            $html .= "    <input type=\"hidden\" name=\"form_id\" value=\"{$model->uuid}\">\n";
            
            foreach ($fields as $field) {
                $label = ucfirst(str_replace('_', ' ', $field));
                $html .= "    <div class=\"form-group\">\n";
                $html .= "        <label for=\"{$field}\">{$label}</label>\n";
                $html .= "        <input type=\"text\" id=\"{$field}\" name=\"{$field}\" required>\n";
                $html .= "    </div>\n";
            }
            $html .= "    <button type=\"submit\">Enviar</button>\n";
            $html .= "</form>\n\n";
            
            $html .= "<script>\n";
            $html .= "document.getElementById('custom-form-{$model->uuid}').addEventListener('submit', async function(e) {\n";
            $html .= "    e.preventDefault();\n";
            $html .= "    const formData = new FormData(this);\n";
            $html .= "    const data = Object.fromEntries(formData.entries());\n\n";
            
            $html .= "    try {\n";
            $html .= "        const response = await fetch('/api/forms/submit', {\n";
            $html .= "            method: 'POST',\n";
            $html .= "            headers: {\n";
            $html .= "                'Content-Type': 'application/json',\n";
            $html .= "                'Accept': 'application/json'\n";
            $html .= "            },\n";
            $html .= "            body: JSON.stringify(data)\n";
            $html .= "        });\n\n";
            
            $html .= "        const result = await response.json();\n";
            $html .= "        if (result.success) {\n";
            $html .= "            alert('¡Enviado con éxito!');\n";
            $html .= "            this.reset();\n";
            $html .= "        } else {\n";
            $html .= "            alert('Error: ' + (result.message || 'Ocurrió un problema'));\n";
            $html .= "        }\n";
            $html .= "    } catch (error) {\n";
            $html .= "        console.error('Error:', error);\n";
            $html .= "        alert('Error de conexión.');\n";
            $html .= "    }\n";
            $html .= "});\n";
            $html .= "</script>";
            
            $model->description = $html;
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function results()
    {
        return $this->hasMany(FormResult::class, 'form_id');
    }
}
