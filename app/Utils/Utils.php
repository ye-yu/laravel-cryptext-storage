<?php

namespace App\Utils;

use App\Models\User;
use InvalidArgumentException;

class Utils {
    static function nonNullString(?string $obj): string {
        if ($obj === null) throw new InvalidArgumentException();
        return $obj;
    }

    static function user($request): User
    {
        return $request->user();
    }
}
