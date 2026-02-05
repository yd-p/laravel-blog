<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeOptions extends Model
{
    protected $fillable = ['theme_id', 'key', 'value'];
}
