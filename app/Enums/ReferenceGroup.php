<?php

namespace App\Enums;

enum ReferenceGroup: string
{
    case Sector = 'sector';
    case ProjectType = 'project_type';
    case AssetCategory = 'asset_category';
    case FixedCostCategory = 'fixed_cost_category';
    case JobCategory = 'job_category';
}
