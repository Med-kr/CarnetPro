<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_global_admin',
        'is_banned',
        'reputation',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_global_admin' => 'boolean',
            'is_banned' => 'boolean',
        ];
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

    public function activeFlatshareMembership(): HasOneThrough
    {
        return $this->hasOneThrough(
            Flatshare::class,
            Membership::class,
            'user_id',
            'id',
            'id',
            'flatshare_id'
        )->whereNull('memberships.left_at');
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

    public function isOwnerOf(Flatshare $flatshare): bool
    {
        return $flatshare->owner_id === $this->id;
    }
}
