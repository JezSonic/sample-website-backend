<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @method static where(string $string, string $operator, mixed $value)
 * @method static updateOrCreate(array $array, array $array1)
 */
class UserProfileSettings extends Model {
    use HasFactory;
    public $timestamps = false;
    protected $table = 'users_profile_settings';
    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'timezone',
        'id',
        'avatar_source',
        'is_public',
        'email_notifications',
        'email_marketing',
        'email_security_alerts'
    ];
}
