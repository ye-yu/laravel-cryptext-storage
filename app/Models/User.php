<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property-read Collection $keySlot;
 * @property-read Collection $secrets;
 * @property-read DatabaseNotification $unreadNotifications;
*/
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function keySlot(): HasMany
    {
        return $this->hasMany('App\Models\KeySlot');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
        ];
    }

    public function userHasKeySlotInstance(): bool
    {
        try {
            return count($this->keySlot->all()) > 0;
        } catch (ModelNotFoundException) {
            return false;
        }
    }

    /**
     * @param string[] $unlockingKeys
     * @return KeySlot
     * @throws Exception
     */
    public function createNewKey(array $unlockingKeys): KeySlot
    {
        return KeySlot::createNewInstance($unlockingKeys, $this);
    }

    public function secrets(): HasMany
    {
        return $this->hasMany('App\Models\Secret', 'user_id');
    }


    /**
     * returns all secrets
     */
    #[ArrayShape(["notes" => "App\\Models\\Secret[]"])] public function getAllNotes(): array
    {
        return [
            "notes" => $this->secrets->all()
        ];
    }

    #[ArrayShape([
        "keys" => ["slot0" => "\null|string", "slot1" => "\null|string", "slot2" => "\null|string", "slot3" => "\null|string", "slot4" => "\null|string", "slot5" => "\null|string", "slot6" => "\null|string", "slot7" => "\null|string"]
    ])]
    public function getKeySlotsInfo(): array
    {
        try {
            $keySlot = $this->getLatestKeySlot();
            return [
                'keys' => $keySlot->getStoredKeysInfo(),
            ];
        } catch (ModelNotFoundException) {
            return [
                'keys' => [
                    "slot0" => null,
                    "slot1" => null,
                    "slot2" => null,
                    "slot3" => null,
                    "slot4" => null,
                    "slot5" => null,
                    "slot6" => null,
                    "slot7" => null,
                ],
            ];
        }
    }

    /**
     * @throws ModelNotFoundException
     * @return KeySlot
     */
    public function getLatestKeySlot(): KeySlot
    {
        return KeySlot::asSelf($this->keySlot()->orderBy(Model::CREATED_AT, 'desc')->firstOrFail());
    }
}
