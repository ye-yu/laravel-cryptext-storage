<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keys\CreateKeyRequest;
use App\Http\Requests\Keys\RotateKeyRequest;
use App\Jobs\KeyRotatorJob;
use App\Utils\Utils;
use Exception;
use Illuminate\Http\JsonResponse;
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

    #[ArrayShape(["message" => "string"])]
    function rotateKey(RotateKeyRequest $request): array
    {
        $user = Utils::user($request);
        $slots = $request->validatedSlotArrays();
        $unlockingKey = $request->validatedUnlockingKey();

        KeyRotatorJob::dispatch($user, $unlockingKey, $slots);

        return [
            "message" => "Job dispatched."
        ];
    }
}
