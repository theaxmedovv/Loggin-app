<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
