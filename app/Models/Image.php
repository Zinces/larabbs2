<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'images';

    protected $fillable = ['path','user_id','type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
