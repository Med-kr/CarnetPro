<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'colocation_id',
        'name',
    ];

    public function colocation()
    {
        return $this->belongsTo(colocation::class);
    }

    public function expenses()
    {
        return $this->hasMany(expense::class);
    }
}
