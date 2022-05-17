<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;

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
     * @var int there are 8 key slots that is available
     */
    public static int $SLOTS = 8;

    public function checkIfAlreadyExists(string $keyToCheck): bool
    {
        $i = 0;
        $slotName = "";
        while($i < KeySlot::$SLOTS) {
            $slotName = "slot" . $i;
            $i += 1;

            $key = $this->{$slotName};
            if (strlen($key) === 0) continue;
            $keyMatched = Hash::check($keyToCheck, $key);
            if ($keyMatched) return true;
        }
        return false;
    }

    /**
     * @throws Exception if there is no empty slot
     */
    public function tryAddNewKey(string $newKey) {
        $i = 0;
        $slotName = "";
        $slotIsFull = false;
        while($i < KeySlot::$SLOTS) {
            $slotName = "slot" . $i;
            $key = $this->{$slotName};
            if (strlen($key) === 0) break;
            $slotIsFull = $i === (KeySlot::$SLOTS - 1);
            $i += 1;
        }

        if ($slotIsFull) throw new Exception("No slot is empty.");

        $hashedKey = Hash::make($newKey);

        $this->{$slotName} = $hashedKey;
        $this->save();
    }

    public function keyOwner(): HasOne
    {
        return $this->hasOne("App\Models\User");
    }
}
