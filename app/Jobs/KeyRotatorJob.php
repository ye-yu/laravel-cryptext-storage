<?php

namespace App\Jobs;

use App\Models\KeySlot;
use App\Models\Secret;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class KeyRotatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $newUnlockingKeyCandidate;

    /**
     * Create a new job instance.
     *
     * @return void
     * @throws Exception
     */
    public function __construct(private User $user, private string $unlockingKey, private array $slots)
    {
        $this->newUnlockingKeyCandidate = "";
        foreach ($this->slots as $slot) {
            $this->newUnlockingKeyCandidate = strlen($slot) > 0 ? $slot : $this->newUnlockingKeyCandidate;
        }

        if (strlen($this->newUnlockingKeyCandidate)) throw new Exception("Cannot find candidate key to decrypt master key!");
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $notes = Secret::asSelfArray(Secret::where('user_id', $this->user->id)->get());

        $keySlot = KeySlot::createNewInstance($this->slots, $this->user);

        foreach ($notes as $note) {
            $note->rotateEncryption($this->unlockingKey, $this->newUnlockingKeyCandidate, $keySlot);
        }
    }
}
