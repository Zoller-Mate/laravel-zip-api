<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{
    use HasFactory;

    protected $fillable = ['postal_code', 'place_id'];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}

