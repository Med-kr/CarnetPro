<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colocation extends Model
{
    use HasFactory;

    // Champs qu'on peut remplir (mass assignment)
    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'status',
    ];

    // Une colocation a un owner (user)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Une colocation a plusieurs memberships
    public function memberships()
    {
        return $this->hasMany(membership::class);
    }

    // Les membres (users) via la table pivot memberships
    public function members()
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->withPivot(['role', 'reputation_score', 'left_at'])
            ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(invitations::class);
    }

    public function categories()
    {
        return $this->hasMany(category::class);
    }

    public function expenses()
    {
        return $this->hasMany(expense::class);
    }

    public function payments()
    {
        return $this->hasMany(payment::class);
    }
}
