<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $column, string $operator, mixed $value)
 * @method static select(array $columns)
 */
class UserLoginActivity extends Model {
    use HasFactory;
    protected $fillable = [
        'user_id',
        'ip_address',
        'location',
        'user_agent',
        'login_method'
    ];

    protected $table = 'user_login_activities';

    /**
     * Get the user that owns the login activity.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
