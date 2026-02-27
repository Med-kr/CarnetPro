<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'colocation_id',
        'user_id',
        'role',
        'reputation_score',
        'left_at',
    ];

    protected $casts = [
        'left_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function colocation()
    {
        return $this->belongsTo(colocation::class);
    }
}
