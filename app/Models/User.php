<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        return $this->hasMany(GiftSuggestion::class);
    }

    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function getAllFamilyMembers()
    {
        if ($this->family_id) {
            return self::where('family_id', $this->family_id)->where('id', '!=', $this->id)->get();
        }
        return collect();
    }

    public function isFamilyWith(User $user)
    {
        return $this->family_id && $this->family_id === $user->family_id;
    }

    public function secretSantaAssignment()
    {
        return $this->hasOne(SecretSantaAssignment::class, 'giver_id');
    }
}
