<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $password
 * @property string $email
 * @property string $name
 * @property string $salt
 * @property int $id
 * @method static where(string $string, string $operator, mixed $value)
 * @method static updateOrCreate(array $array, array $array1)
 */
class User extends Authenticatable implements MustVerifyEmail {
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'github_id',
        'github_token',
        'github_refresh_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'salt',
        'remember_token',
    ];

    public function getSalt(): string {
        return $this->salt;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime'
        ];
    }

    public function googleData(): HasOne {
        return $this->hasOne(GoogleUserData::class);
    }

    public function gitHubData(): HasOne {
        return $this->hasOne(GitHubUserData::class);
    }
}
