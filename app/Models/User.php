<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_global_admin',
        'is_banned',
        'banned_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_global_admin' => 'boolean',
        'is_banned' => 'boolean',
        'banned_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function (User $user) {
            if (User::count() === 1) {
                $user->is_global_admin = true;
                $user->save();
            }
        });
    }

    // Relations
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function colocations()
    {
        return $this->belongsToMany(Colocation::class, 'memberships')
            ->withPivot(['role', 'reputation_score', 'left_at'])
            ->withTimestamps();
    }

    public function ownedColocations()
    {
        return $this->hasMany(Colocation::class, 'owner_id');
    }

    public function isGlobalAdmin()
    {
        return $this->is_global_admin === true;
    }

    public function isBanned()
    {
        return $this->is_banned === true;
    }

    public function isOwnerOfColocation($colocationId)
    {
        return $this->memberships()
            ->where('colocation_id', $colocationId)
            ->whereNull('left_at')
            ->where('role', 'owner')
            ->exists();
    }

    public function isActiveMemberOfColocation($colocationId)
    {
        return $this->memberships()
            ->where('colocation_id', $colocationId)
            ->whereNull('left_at')
            ->exists();
    }
}
