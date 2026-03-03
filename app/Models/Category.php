<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    use HasFactory;

    public const DEFAULT_ICON = 'tag';

    protected $fillable = [
        'flatshare_id',
        'name',
        'icon',
    ];

    public static function iconOptions(): array
    {
        return [
            'tag' => ['emoji' => '🏷️', 'label' => 'General'],
            'groceries' => ['emoji' => '🛒', 'label' => 'Groceries'],
            'rent' => ['emoji' => '🏠', 'label' => 'Rent'],
            'utilities' => ['emoji' => '💡', 'label' => 'Utilities'],
            'internet' => ['emoji' => '📶', 'label' => 'Internet'],
            'transport' => ['emoji' => '🚌', 'label' => 'Transport'],
            'food' => ['emoji' => '🍽️', 'label' => 'Food'],
            'cleaning' => ['emoji' => '🧽', 'label' => 'Cleaning'],
            'health' => ['emoji' => '💊', 'label' => 'Health'],
            'fun' => ['emoji' => '🎉', 'label' => 'Fun'],
        ];
    }

    public static function defaultDefinitions(): array
    {
        return [
            ['name' => 'Groceries', 'icon' => 'groceries'],
            ['name' => 'Rent', 'icon' => 'rent'],
            ['name' => 'Utilities', 'icon' => 'utilities'],
            ['name' => 'Internet', 'icon' => 'internet'],
            ['name' => 'Transport', 'icon' => 'transport'],
            ['name' => 'Food', 'icon' => 'food'],
            ['name' => 'Cleaning', 'icon' => 'cleaning'],
            ['name' => 'Health', 'icon' => 'health'],
            ['name' => 'Fun', 'icon' => 'fun'],
        ];
    }

    public static function ensureDefaultsForFlatshare(int $flatshareId): Collection
    {
        return collect(static::defaultDefinitions())->map(function (array $definition) use ($flatshareId) {
            return static::firstOrCreate(
                [
                    'flatshare_id' => $flatshareId,
                    'name' => $definition['name'],
                ],
                [
                    'icon' => $definition['icon'],
                ]
            );
        });
    }

    public function iconEmoji(): string
    {
        return static::iconOptions()[$this->icon ?? static::DEFAULT_ICON]['emoji']
            ?? static::iconOptions()[static::DEFAULT_ICON]['emoji'];
    }

    public function iconLabel(): string
    {
        return static::iconOptions()[$this->icon ?? static::DEFAULT_ICON]['label']
            ?? static::iconOptions()[static::DEFAULT_ICON]['label'];
    }

    public function flatshare(): BelongsTo
    {
        return $this->belongsTo(Flatshare::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
