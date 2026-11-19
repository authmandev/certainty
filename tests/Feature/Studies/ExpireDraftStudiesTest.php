<?php

use App\Enums\StudyStatus;
use App\Models\Study;

test('drafts past their deadline are marked expired', function () {
    $overdue = Study::factory()->expired()->create();
    $active = Study::factory()->create();
    $finalized = Study::factory()->finalized()->create(['expires_at' => now()->subDays(5)]);

    $this->artisan('studies:expire-drafts')->assertSuccessful();

    expect($overdue->fresh()->status)->toBe(StudyStatus::Expired)
        ->and($active->fresh()->status)->toBe(StudyStatus::Draft)
        ->and($finalized->fresh()->status)->toBe(StudyStatus::Finalized);
});
