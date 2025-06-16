<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, string $string1, int $id)
 */
class UserDataExports  extends Model {
    use HasFactory;
    protected $fillable = [
        'user_id',
        'valid_until',
        'status',
    ];

    protected $table = 'user_data_exports';
    public $timestamps = false;

    public function user(): BelongsTo {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
