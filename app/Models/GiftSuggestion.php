<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'suggestion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
