<?php

namespace App\Http\Controllers;

use App\Http\Requests\Secrets\CreateNoteRequest;
use App\Http\Requests\Secrets\ReadNoteRequest;
use App\Models\Secret;
use App\Utils\Utils;
use Illuminate\Http\Request;
use MiladRahimi\PhpCrypt\Exceptions\DecryptionException;
use MiladRahimi\PhpCrypt\Exceptions\EncryptionException;

class SecretController extends Controller
{
    /**
     * @throws DecryptionException
     * @throws EncryptionException
     */
    function createNewNote(CreateNoteRequest $request) {
        $user = Utils::user($request);
        abort_if(!$user->userHasKeySlotInstance(), 400, "There is no available key slots to use.");
        $formData = $request->validatedBody();
        return Secret::createNewNote($formData["key"], $formData["name"], $formData["content"], $user);
    }

    function readNote(ReadNoteRequest $request, string $name) {
        $user = Utils::user($request);
        abort_if(!$user->userHasKeySlotInstance(), 400, "There is no available key slots to use.");
        $formData = $request->validatedBody();
        return Secret::readNote($formData["key"], $name, $user);
    }
}
