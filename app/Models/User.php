<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'dni',
        'phone',
        'email',
        'password',
        'is_admin',
        'profile_photo_path',
        'funny_profile_photo_path',
        'family_id',
        'family_group_id',
        'reset_token',
        'reset_expires_at',
        'nickname',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
        ];
    }

    public function giftSuggestions(): HasMany
    {
        $relation = $this->hasMany(GiftSuggestion::class);
        if ($this->family_group_id) {
            $relation->where('family_group_id', $this->family_group_id);
        }
        return $relation;
    }

    public function allGiftSuggestions(): HasMany
    {
        return $this->hasMany(GiftSuggestion::class);
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function familyGroups(): BelongsToMany
    {
        return $this->belongsToMany(FamilyGroup::class, 'family_group_user')->withTimestamps();
    }

    public function getAllFamilyMembers($familyGroupId = null)
    {
        if (!$this->family_id) {
            return collect();
        }

        $query = self::where('family_id', $this->family_id)->where('id', '!=', $this->id);

        if ($familyGroupId) {
            $query->where(function ($q) use ($familyGroupId) {
                $q->where('family_group_id', $familyGroupId)
                  ->orWhereHas('familyGroups', function ($fgQuery) use ($familyGroupId) {
                      $fgQuery->where('family_groups.id', $familyGroupId);
                  });
            });
        }

        return $query->get();
    }

    public function isFamilyWith(User $user)
    {
        return $this->family_id && $this->family_id === $user->family_id;
    }

    public function secretSantaAssignment()
    {
        $relation = $this->hasOne(SecretSantaAssignment::class, 'giver_id');
        if ($this->family_group_id) {
            $relation->where('family_group_id', $this->family_group_id);
        }
        return $relation;
    }

    public function secretSantaAssignments(): HasMany
    {
        return $this->hasMany(SecretSantaAssignment::class, 'giver_id');
    }
}
