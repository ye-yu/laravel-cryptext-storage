<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use MiladRahimi\PhpCrypt\Exceptions\DecryptionException;
use MiladRahimi\PhpCrypt\Exceptions\EncryptionException;
use MiladRahimi\PhpCrypt\Symmetric;

/**
 * @property-read User $keyOwner
 * @property-read string $slot0
 * @property-read string $slot1
 * @property-read string $slot2
 * @property-read string $slot3
 * @property-read string $slot4
 * @property-read string $slot5
 * @property-read string $slot6
 * @property-read string $slot7
 * @property-read string $key0
 * @property-read string $key1
 * @property-read string $key2
 * @property-read string $key3
 * @property-read string $key4
 * @property-read string $key5
 * @property-read string $key6
 * @property-read string $key7
 * @property-read int $id
 * @method static Collection where(string $name, mixed $value)
 */
class KeySlot extends Model
{
    use HasFactory;
    use HasTimestamps;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'slot0',
        'slot1',
        'slot2',
        'slot3',
        'slot4',
        'slot5',
        'slot6',
        'slot7',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'slot0',
        'slot1',
        'slot2',
        'slot3',
        'slot4',
        'slot5',
        'slot6',
        'slot7',
    ];

    /**
     * @return string[]
     */
    public function getSlotArray(): array
    {
        return [
            $this->slot0,
            $this->slot1,
            $this->slot2,
            $this->slot3,
            $this->slot4,
            $this->slot5,
            $this->slot6,
            $this->slot7,
        ];
    }

    public function getKeyArray(): array
    {
        return [
            $this->key0,
            $this->key1,
            $this->key2,
            $this->key3,
            $this->key4,
            $this->key5,
            $this->key6,
            $this->key7,
        ];
    }

    /**
     * @param string $key
     * @return int matching key slot
     */
    public function getMatchingKeySlot(string $key): int
    {
        foreach ($this->getSlotArray() as $i => $hashedKey) {
            if (strlen($hashedKey) === 0) continue;
            if (Hash::check($key, $hashedKey)) return $i;
        }

        return -1;
    }

    public function getFirstOfEmpty(): int
    {
        foreach ($this->getSlotArray() as $i => $hashedKey) {
            if (strlen($hashedKey) === 0) return $i;
        }
        return -1;
    }

    /**
     * @throws Exception
     */
    public function appendNewKey(string $newKey, string $unlockingKey) {
        if (($firstOfEmpty = $this->getFirstOfEmpty()) < 0) throw new Exception("there is no available slot");
        if (strcmp($newKey, $unlockingKey) === 0) throw new Exception("new key is the same as the unlocking key");
        $masterKey = $this->decryptMasterKey($unlockingKey)["masterKey"];
        $this->addEncryptedKey($firstOfEmpty, $newKey, $masterKey);
    }

    /**
     * @throws EncryptionException
     */
    private function addEncryptedKey(int $slotNumber, string $encryptionKey, string $masterKey) {
        $encryptor = new Symmetric($encryptionKey);
        $encryptedKey = $encryptor->encrypt($masterKey);
        $keyAttr = "key" . $slotNumber;
        $this->{$keyAttr} = $encryptedKey;

        $slotAttr = "slot" . $slotNumber;
        $hashedKey = Hash::make($encryptionKey);
        $this->{$slotAttr} = $hashedKey;
        $this->save();
    }

    public function keyOwner(): BelongsTo
    {
        return $this->belongsTo("App\Models\User", "user_id");
    }

    /**
     * @param string $unlockingKey
     * @return array
     * @throws DecryptionException
     * @throws Exception
     */
    #[ArrayShape(["masterKey" => "string", "matchedSlot" => "int"])]
    private function decryptMasterKey(string $unlockingKey): array
    {
        $matchedSlot = $this->getMatchingKeySlot($unlockingKey);
        if ($matchedSlot < 0) throw new Exception("unlocking key does not match any existing keys");
        $encryptedKey = $this->getKeyArray()[$matchedSlot];
        $decryptor = new Symmetric($unlockingKey);
        return [
            "masterKey" => $decryptor->decrypt($encryptedKey),
            "matchedSlot" => $matchedSlot,
        ];
    }


    /**
     * @throws DecryptionException
     */
    public function decryptContent(string $unlockingKey, string $content): string
    {
        $masterKey = $this->decryptMasterKey($unlockingKey)["masterKey"];
        $decryptor = new Symmetric($masterKey);
        return $decryptor->decrypt($content);
    }

    /**
     * @throws DecryptionException|EncryptionException
     */
    public function encryptContent(string $unlockingKey, string $content): string
    {
        $masterKey = $this->decryptMasterKey($unlockingKey)["masterKey"];
        $decryptor = new Symmetric($masterKey);
        return $decryptor->encrypt($content);
    }

    public static function asSelf(mixed $any): KeySlot
    {
        return $any;
    }

    /**
     * @param string[] $keys
     * @param User $keyOwner
     * @return KeySlot
     * @throws Exception
     */
    public static function createNewInstance(array $keys, User $keyOwner): KeySlot
    {
        if (count($keys) > 8) throw new Exception("can only support up to 8 unlocking keys");
        if (count($keys) < 1) throw new Exception("must provide at least one unlocking keys");

        $keySlot = KeySlot::asSelf(KeySlot::factory()->create([
            "user_id" => $keyOwner->id
        ]));

        $masterKey = Symmetric::generateKey();

        foreach ($keys as $i => $key) {
            $keySlot->addEncryptedKey($i, $key, $masterKey);
        }

        return $keySlot;
    }

    #[ArrayShape(["slot0" => "null|string", "slot1" => "null|string", "slot2" => "null|string", "slot3" => "null|string", "slot4" => "null|string", "slot5" => "null|string", "slot6" => "null|string", "slot7" => "null|string"])]
    public function getStoredKeysInfo(): array
    {
        return [
            "slot0" => $this->slot0 ? strlen($this->slot0) : null,
            "slot1" => $this->slot1 ? strlen($this->slot1) : null,
            "slot2" => $this->slot2 ? strlen($this->slot2) : null,
            "slot3" => $this->slot3 ? strlen($this->slot3) : null,
            "slot4" => $this->slot4 ? strlen($this->slot4) : null,
            "slot5" => $this->slot5 ? strlen($this->slot5) : null,
            "slot6" => $this->slot6 ? strlen($this->slot6) : null,
            "slot7" => $this->slot7 ? strlen($this->slot7) : null,
        ];
    }
}
