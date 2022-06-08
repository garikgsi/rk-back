<?php

namespace App\Services\Repositories;

use App\Models\KidParent;
use App\Services\Repositories\KidRepository;
use App\Services\TableRepositoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KidParentRepository extends TableRepositoryService
{
    /**
     * current request
     */
    protected Request $request;

    /**
     * use personal constructor with specified fill repository data
     *
     * @param  KidsRepository $kids
     * @return void
     */
    public function __construct(KidRepository $kids) {
        $this->use('kid_parents');
        $this->fillRepository(KidParent::whereIn('kid_id', $kids->getIds()));
    }


}
