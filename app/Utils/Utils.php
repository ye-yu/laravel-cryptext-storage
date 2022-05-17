<?php

namespace App\Utils;

use http\Exception\InvalidArgumentException;

class Utils {
    static function nonNullString(?string $obj): string {
        if ($obj === null) throw new InvalidArgumentException();
        return $obj;
    }
}
