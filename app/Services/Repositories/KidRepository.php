<?php

namespace App\Services\Repositories;

use App\Models\Kid;
use App\Services\TableRepositoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KidRepository extends TableRepositoryService
{
    /**
     * use personal constructor with specified fill repository data
     *
     * @param  OrganizationRepository $organizations
     * @return void
     */
    public function __construct(OrganizationRepository $organizations) {
        $this->use('kids');
        $this->fillRepository(Kid::whereIn('organization_id', $organizations->getIds()));
    }
}
