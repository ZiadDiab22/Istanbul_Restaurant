<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logo extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'img_url', 'selected',
    ];
}
