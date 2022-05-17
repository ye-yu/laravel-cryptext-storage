<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keys\CreateKeyRequest;
use App\Utils\Utils;
use Exception;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class KeysController extends Controller
{
    /**
     * @throws Exception
     */
    #[ArrayShape(["keys" => "array"])]
    function createKey(CreateKeyRequest $request): array
    {
        $user = Utils::user($request);
        abort_if($user->userHasKeySlotInstance(), 400, "User has already created a key slot instance");
        $slots = $request->validatedSlotArrays();
        $user->createNewKey($slots);
        return $user->getKeySlotsInfo();
    }
}
