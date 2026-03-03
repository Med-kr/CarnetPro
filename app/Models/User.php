<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_global_admin' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if (User::count() === 1) {
                $user->is_global_admin = true;
                $user->save();
            }
        });
    }

    public function ownedFlatshares(): HasMany
    {
        return $this->hasMany(Flatshare::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMembership(): HasOne
    {
        return $this->hasOne(Membership::class)->whereNull('left_at');
    }

    public function flatshares(): BelongsToMany
    {
        return $this->belongsToMany(Flatshare::class, 'memberships')
            ->withPivot(['role', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function paidExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'payer_id');
    }

    public function outgoingPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'from_user_id');
    }

    public function incomingPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'to_user_id');
    }

    public function isGlobalAdmin(): bool
    {
        return $this->is_global_admin;
    }

    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_banned', false);
    }

    public function hasActiveFlatshare(): bool
    {
        return $this->memberships()
            ->whereNull('left_at')
            ->whereHas('flatshare', fn (Builder $query) => $query->where('status', Flatshare::STATUS_ACTIVE))
            ->exists();
    }

    public function isOwnerOfFlatshare(Flatshare $flatshare): bool
    {
        return $flatshare->owner_id === $this->id;
    }

    public function isActiveMemberOfFlatshare(Flatshare $flatshare): bool
    {
        return $this->memberships()
            ->where('flatshare_id', $flatshare->id)
            ->whereNull('left_at')
            ->exists();
    }
}
