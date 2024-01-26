<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class delivery_service extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'city_id', 'price', 'blocked'
    ];
}
