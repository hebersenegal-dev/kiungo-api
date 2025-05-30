<?php

// app/Models/Award.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $fillable = ['user_id', 'prize', 'title', 'event', 'year', 'location'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
