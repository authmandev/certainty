<?php

namespace App\Services\Studies;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @phpstan-type SectionProgress array{key: string, title: string, completed: int, total: int, is_complete: bool, missing: list<string>}
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class StudyProgress implements Arrayable
{
    /**
     * @param  list<SectionProgress>  $sections
     */
    public function __construct(
        public array $sections,
        public int $percentage,
    ) {}

    /**
     * Determine whether every required item in every section is filled in.
     */
    public function isComplete(): bool
    {
        return $this->percentage === 100;
    }

    /**
     * @return array{percentage: int, sections: list<SectionProgress>}
     */
    public function toArray(): array
    {
        return [
            'percentage' => $this->percentage,
            'sections' => $this->sections,
        ];
    }
}
