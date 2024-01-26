<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name', 'type_id', 'disc', 'price', 'quantity',
        'source_price', 'code', 'img_url', 'long_disc'
    ];

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }
}
