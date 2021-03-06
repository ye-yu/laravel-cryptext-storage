<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JetBrains\PhpStorm\ArrayShape;
use MiladRahimi\PhpCrypt\Exceptions\DecryptionException;
use MiladRahimi\PhpCrypt\Exceptions\EncryptionException;

/**
 * @property-read string $content
 * @property-read int $key_id
 * @property-read Collection $keySlot
 * @property-read User $fileOwner
 * @method static Collection where(string $name, mixed $value)
 *
 * @extends Model
 */
class Secret extends Model
{
    use HasFactory;

    protected $hidden = [
        "content",
    ];

    protected $fillable = [
        "key_id",
        "content",
        "keySlot",
    ];

    public static function asSelf(mixed $any): Secret
    {
        return $any;
    }

    /**
     * @param mixed $any
     * @return Secret[]
     */
    public static function asSelfArray(mixed $any): array
    {
        return $any;
    }

    /**
     * @throws DecryptionException
     */
    #[ArrayShape(["content" => "string"])]
    public static function readNote(string $key, mixed $name, User $user): array
    {
        return [
            "content" => Secret::asSelf(Secret::where('name', $name)
                ->where("user_id", $user->id)->firstOrFail())->tryDecrypt($key)
        ];
    }

    public function keySlot(): BelongsTo
    {
        return $this->belongsTo('App\Models\KeySlot', 'key_id');
    }

    public function fileOwner(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function getKeySlot(): KeySlot {
        $currentKeySlot = $this->keySlot->firstOrFail();
        if ($currentKeySlot->id !== $this->key_id) {
            // fuck you laravel for making this too many work
            $currentKeySlot = KeySlot::where("id", $this->key_id)->firstOrFail();
        }
        return $currentKeySlot;
    }

    /**
     * @throws DecryptionException
     */
    public function tryDecrypt(string $key): string
    {
        $keySlot = $this->getKeySlot();
        return $keySlot->decryptContent($key, $this->content);
    }

    /**
     * @throws DecryptionException
     * @throws EncryptionException
     */
    public function rotateEncryption(string $unlockingKey, string $newUnlockingKey, KeySlot $newKeySlot): bool
    {
        $unlockedNote = $this->tryDecrypt($unlockingKey);
        $newEncrypted = $newKeySlot->encryptContent($newUnlockingKey, $unlockedNote);
        $success = $this->update([
            "key_id" => $newKeySlot->id,
            "content" => $newEncrypted,
        ]);

        $this->load('keySlot');
        return $success;
    }

    /**
     * @throws DecryptionException
     * @throws EncryptionException
     */
    public static function createNewNote(string $key, string $name, string $plainText, User $owner) {
        $keySlot = $owner->getLatestKeySlot();
        $content = $keySlot->encryptContent($key, $plainText);
        return Secret::factory()->create([
            "key_id" => $keySlot->id,
            "user_id" => $owner->id,
            "name" => $name,
            "content" => $content,
        ]);
    }
}
