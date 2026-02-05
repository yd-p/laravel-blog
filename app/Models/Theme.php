<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  Theme extends Model
{
    protected $fillable = ['name', 'folder', 'version','default','author','link','description'];

    public function options()
    {
        return $this->hasMany(ThemeOptions::class, 'theme_id');
    }
}
