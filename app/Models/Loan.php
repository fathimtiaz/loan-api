<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasFactory, HasUuids;

    public const STATE_PENDING = 'PENDING';
    public const STATE_APPROVED = 'APPROVED';
    public const STATE_PAID = 'PAID';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->id = Str::uuid();
    }

    /**
     * Get the terms for the blog post.
     */
    public function terms(): HasMany
    {
        return $this->hasMany(LoanTerm::class);
    }

    /**
     * Get the repayments for the blog post.
     */
    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
