<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, string $operator, mixed $value)
 * @method static updateOrCreate(array $array, array $array1)
 */
class GoogleUserData extends Model {
    protected $table = 'google_user_data';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'google_name',
        'google_email',
        'google_avatar_url',
        'id',
        'google_token',
        'google_token_expires_in',
        'google_refresh_token'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
