<?php

namespace App\Services\Repositories;

use App\Models\Period;
use App\Services\TableRepositoryService;
use Illuminate\Http\Request;

class PeriodRepository extends TableRepositoryService
{
    /**
     * use personal constructor with specified fill repository data
     *
     * @param  OrganizationRepository $organizations
     * @return void
     */
    public function __construct(OrganizationRepository $organizations) {
        $this->use('periods');
        $this->fillRepository(Period::whereIn('organization_id', $organizations->getIds()));
    }


}
