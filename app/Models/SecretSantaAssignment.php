<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretSantaAssignment extends Model
{
    protected $fillable = ['giver_id', 'receiver_id', 'family_group_id'];

    public function giver()
    {
        return $this->belongsTo(User::class, 'giver_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }
}
