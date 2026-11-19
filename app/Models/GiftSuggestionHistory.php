<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftSuggestionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'family_group_id',
        'suggestions',
        'suggested_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'suggestions' => 'array',
            'suggested_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this history snapshot.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the family group associated with this history snapshot.
     */
    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }
}
