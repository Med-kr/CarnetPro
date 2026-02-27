<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'colocation_id',
        'title',
        'amount',
        'expense_date',
        'category_id',
        'paid_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function colocation()
    {
        return $this->belongsTo(colocation::class);
    }

    public function category()
    {
        return $this->belongsTo(category::class);
    }

    // Le user qui a payé
    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }
}
