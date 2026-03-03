<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK = 'bank_transfer';
    public const METHOD_CARD = 'card';
    public const METHOD_MOBILE = 'mobile_wallet';
    public const METHOD_CUSTOM = 'custom';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'flatshare_id',
        'from_user_id',
        'to_user_id',
        'amount',
        'settlement_amount',
        'applied_amount',
        'credit_amount',
        'method',
        'status',
        'reference',
        'note',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'settlement_amount' => 'decimal:2',
            'applied_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public static function methodOptions(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK => 'Bank transfer',
            self::METHOD_CARD => 'Card',
            self::METHOD_MOBILE => 'Mobile wallet',
            self::METHOD_CUSTOM => 'Custom',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function methodLabel(): string
    {
        return static::methodOptions()[$this->method] ?? $this->method;
    }

    public function flatshare(): BelongsTo
    {
        return $this->belongsTo(Flatshare::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
