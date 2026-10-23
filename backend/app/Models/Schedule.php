<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    // A schedule belongs to the user who created it.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A schedule optionally belongs to a category.
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // A schedule can contain many tasks.
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
