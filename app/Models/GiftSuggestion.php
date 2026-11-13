<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'family_group_id',
        'suggestion',
        'reference_image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }
}
