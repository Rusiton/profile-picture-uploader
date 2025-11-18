<?php

namespace App\Models;

use App\Traits\HasToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilePicture extends Model
{
    use HasToken;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'profile_picture_public_id',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
