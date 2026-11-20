<?php

namespace App\Models;

use App\Enums\ReferenceGroup;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A controlled dropdown value (BRD FR-008, A-08). Codes are stable; labels may change.
 *
 * @property int $id
 * @property ReferenceGroup $group
 * @property string $code
 * @property string $label_ar
 * @property bool $is_active
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['group', 'code', 'label_ar', 'is_active', 'sort_order'])]
class ReferenceOption extends Model
{
    /**
     * The code every "other" option uses, which lets the customer type a custom description.
     */
    public const string OTHER = 'other';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'group' => ReferenceGroup::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Limit the query to active options of a group, in display order.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function activeIn(Builder $query, ReferenceGroup $group): void
    {
        $query->where('group', $group)->where('is_active', true)->orderBy('sort_order');
    }
}
