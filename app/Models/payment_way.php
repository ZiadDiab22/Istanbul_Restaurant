<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payment_way extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name', 'blocked'
    ];
}
