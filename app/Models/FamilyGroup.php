<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'enable_draw_at',
        'reveal_date',
        'profile_edit_end_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enable_draw_at' => 'datetime',
            'reveal_date' => 'datetime',
            'profile_edit_end_date' => 'datetime',
        ];
    }

    /**
     * Get the users for the family group.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the secret santa assignments for the family group.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(SecretSantaAssignment::class);
    }

    /**
     * Get the registration URL for this family group.
     */
    public function getRegistrationUrlAttribute(): string
    {
        if ($this->isDefault()) {
            return route('user.register.view');
        }
        return route('user.register.view') . '?fam=' . $this->slug;
    }

    /**
     * Check if this family group has already been drawn.
     */
    public function hasDrawn(): bool
    {
        return $this->assignments()->exists();
    }

    /**
     * Check if the draw can be performed at this time.
     */
    public function canDraw(): bool
    {
        if (!$this->enable_draw_at) {
            return true; // Si no hay fecha configurada, se puede sortear
        }
        return now()->gte($this->enable_draw_at);
    }

    /**
     * Check if the secret santa has been revealed.
     */
    public function isRevealed(): bool
    {
        if (!$this->reveal_date) {
            return false;
        }
        return now()->gte($this->reveal_date);
    }

    /**
     * Check if profiles can still be edited.
     */
    public function canEditProfile(): bool
    {
        if (!$this->profile_edit_end_date) {
            return true; // Si no hay fecha límite, siempre se puede editar
        }
        return now()->lte($this->profile_edit_end_date);
    }

    /**
     * Check if this is the default (original) family group.
     */
    public function isDefault(): bool
    {
        return $this->id === 1 || $this->slug === 'default';
    }

    /**
     * Get the status label for the family group.
     */
    public function getStatusAttribute(): string
    {
        if ($this->hasDrawn()) {
            return 'Sorteo realizado';
        }
        if ($this->canDraw()) {
            return 'Listo para sortear';
        }
        return 'Esperando fecha de sorteo';
    }

    /**
     * Get the registration status.
     */
    public function getRegistrationStatusAttribute(): string
    {
        if ($this->hasDrawn()) {
            return 'Cerrado';
        }
        return 'Abierto';
    }
}