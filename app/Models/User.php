<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lodestone_id',
        'character_name',
        'character_server',
        'character_avatar',
        'verification_code',
        'character_verified_at',
        'job_levels',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'character_verified_at'=> 'datetime',
            'password'             => 'hashed',
            'job_levels'           => 'array',
        ];
    }

    public function isCharacterVerified(): bool
    {
        return $this->character_verified_at !== null;
    }

    public function getJobLevel(int $jobId): int
    {
        return $this->job_levels[$jobId] ?? 0;
    }
}
