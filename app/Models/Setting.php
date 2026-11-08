<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Lecture/écriture : passer par App\Services\Settings\SettingsService (cache + conversion des types)
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];
}
