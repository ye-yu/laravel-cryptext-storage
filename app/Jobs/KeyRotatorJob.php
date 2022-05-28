<?php

namespace App\Jobs;

use App\Models\ApplicationLog;
use App\Models\KeySlot;
use App\Models\Secret;
use App\Models\User;
use App\Notifications\KeyRotationStatus;
use App\Utils\Constants;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MiladRahimi\PhpCrypt\Exceptions\DecryptionException;
use MiladRahimi\PhpCrypt\Exceptions\EncryptionException;

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

        if (strlen($this->newUnlockingKeyCandidate) === 0) throw new Exception("Cannot find candidate key to decrypt master key!");
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @noinspection PhpParamsInspection */
        $notes = Secret::where('user_id', $this->user->id)->get()->all();

        try {
            $keySlot = $this->user->createNewKey($this->slots);
        } catch (Exception $e) {
            ApplicationLog::new(Constants::$KeyRotator_UnableToCreateNewKey, "Error at KeyRotatorJob: " . $e, $this->user);
            $this->user->notify(new KeyRotationStatus(false, time(), 0, 0, "New key slot failed to be initialised. Please escalate this to the administrator with the following report code: " . Constants::$KeyRotator_UnableToCreateNewKey));
            return;
        }

        $failedEncryption = 0;
        $failedDecryption = 0;
        $rotated = 0;
        $total = 0;

        foreach ($notes as $_note) {
            $note = Secret::asSelf($_note);
            $total += 1;
            try {
                $success = $note->rotateEncryption($this->unlockingKey, $this->newUnlockingKeyCandidate, $keySlot);
                $rotated += $success ? 1 : 0;
            } catch (DecryptionException $e) {
                ApplicationLog::new(Constants::$KeyRotator_FailedDecryption, "Error at KeyRotatorJob: " . $e, $this->user);
                $failedDecryption += 1;
            } catch (EncryptionException $e) {
                ApplicationLog::new(Constants::$KeyRotator_FailedEncryption, "Error at KeyRotatorJob: " . $e, $this->user);
                $failedEncryption += 1;
            }
        }

        if ($failedDecryption > 0 && $failedEncryption > 0) {
            $this->user->notify(new KeyRotationStatus(false, time(), $rotated, $total, "Process during some decryption and encryption failed. Please escalate this to the administrator with the following report code: " . implode([Constants::$KeyRotator_FailedDecryption, Constants::$KeyRotator_FailedEncryption])));
        } else if ($failedDecryption) {
            $this->user->notify(new KeyRotationStatus(false, time(), $rotated, $total, "Process during some decryption failed. Please escalate this to the administrator with the following report code: " . implode([Constants::$KeyRotator_FailedDecryption])));
        } else if ($failedEncryption) {
            $this->user->notify(new KeyRotationStatus(false, time(), $rotated, $total, "Process during some encryption failed. Please escalate this to the administrator with the following report code: " . implode([Constants::$KeyRotator_FailedEncryption])));
        } else {
            $this->user->notify(new KeyRotationStatus(true, time(), $rotated, $total));
        }
    }
}
