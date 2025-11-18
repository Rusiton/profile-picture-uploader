<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasToken 
{
    protected static function bootHasToken()
    {
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = $model->generateUniqueToken();
            }
        });
    }

    public function generateUniqueToken()
    {
        do {
            $token = Str::random(32);
        } while (static::where('token', $token)->exists());
        
        return $token;
    }

    public function getRouteKeyName()
    {
        return 'token'; 
    }
}

?>