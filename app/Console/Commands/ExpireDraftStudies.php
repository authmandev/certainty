<?php

namespace App\Console\Commands;

use App\Enums\StudyStatus;
use App\Models\Study;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('studies:expire-drafts')]
#[Description('Mark draft studies whose completion deadline has passed as expired')]
class ExpireDraftStudies extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = Study::query()
            ->where('status', StudyStatus::Draft)
            ->where('expires_at', '<=', now())
            ->update(['status' => StudyStatus::Expired]);

        $this->info("Expired {$expired} draft studies.");

        return self::SUCCESS;
    }
}
