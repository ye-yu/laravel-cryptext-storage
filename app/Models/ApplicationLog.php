<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationLog extends Model
{
    use HasFactory;

    public static function new(string $code, string $message, ?User $user = null)
    {
        if ($user !== null) {
            ApplicationLog::factory()->create([
                "code" => $code,
                "content" => $message,
                "user_id" => $user->id,
            ]);
        } else {
            ApplicationLog::factory()->create([
                "code" => $code,
                "content" => $message,
            ]);
        }
    }
}
