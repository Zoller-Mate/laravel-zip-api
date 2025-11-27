<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{
    protected $fillable = ['postal_code', 'place_id'];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}

