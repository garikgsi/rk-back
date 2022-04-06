<?php

namespace App\Http\Controllers;

// use App\Services\TableRepositoryService;
use Illuminate\Http\Request;
use App\Facades\Table;
use Illuminate\Http\Response;

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

    // /**
    //  * show rows list
    //  *
    //  * @param  mixed $request
    //  */
    // public function index(Request $request) {
    /**
     * show rows list
     *
     * @param  mixed $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request): Response {
        // return $this->repository->show($request);
        return response()->formatApi([
            'data'=>$this->repository->show($request)
        ]);
    }


    // /**
    //  * show row identified by $id
    //  *
    //  * @param  string $table
    //  * @param  mixed $id
    //  */
    // public function show(string $table, $id) {
    /**
     * show row identified by $id
     *
     * @param  string $table
     * @param  mixed $id
     * @return Illuminate\Http\Response
     */
    public function show(string $table, $id): Response {
        // return $this->repository->find($table,(int)$id);
        return response()->formatApi([
            'data' => $this->repository->find($table,(int)$id)
        ]);
    }

    public function update(Request $request, string $table, $id): Response {
        return response()->formatApi([
            'data' => $this->repository->update($request, $table, (int)$id)
        ]);
    }
}
