<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreTheme extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'primary_color',
        'secondary_color',
        'background_color',
        'text_color',
        'font_family',
        'custom_css',
        'background_image',
        'logo',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getCssVariablesAttribute()
    {
        return [
            '--primary-color' => $this->primary_color,
            '--secondary-color' => $this->secondary_color,
            '--background-color' => $this->background_color,
            '--text-color' => $this->text_color,
            '--font-family' => $this->font_family,
        ];
    }

    public function getCustomStylesAttribute()
    {
        $styles = '';
        foreach ($this->css_variables as $var => $value) {
            $styles .= "{$var}: {$value}; ";
        }
        return $styles;
    }
}
