<?php

namespace App\Http\Controllers;

// use App\Services\TableRepositoryService;
use Illuminate\Http\Request;
use App\Facades\Table;

/**
 * Main API controller for abstract table TableInterface
 */
class TableController extends Controller
{
    /**
     * Repository
     *
     * @var App\Services\TableRepositoryService
     */
    protected $repository;

    /**
     * __construct
     *
     * @param  Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->repository = Table::use($request->table);
    }

    /**
     * show rows list
     *
     * @param  Request $request
     * @return void
     */
    public function index(Request $request) {
        return $this->repository->show($request);
    }
}
