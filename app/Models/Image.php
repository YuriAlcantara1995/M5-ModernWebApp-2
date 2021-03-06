<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo('App\Models\Property');
    }

    public function thumbnail()
    {
        return $this->hasOne('App\Models\Thumbnail');
    }
}
