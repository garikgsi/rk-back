<?php

namespace App\Http\Controllers;

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

    /**
     * show rows list
     *
     * @param  mixed $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request): Response {
        return response()->formatApi($this->repository->show($request));
    }

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

    /**
     * update row identified by $id
     *
     * @param  Illuminate\Http\Request $request
     * @param  string $table
     * @param  string|int $id
     * @return Response
     */
    public function update(Request $request, string $table, $id): Response {
        return response()->formatApi([
            'data' => $this->repository->update($request, $table, (int)$id)
        ]);
    }

    /**
     * insert new record to $table
     *
     * @param  Illuminate\Http\Request $request
     * @param  string $table
     * @param  int|string|null $id
     * @return Response
     */
    public function store(Request $request, string $table, int|string|null $id=null): Response {
        return response()->formatApi([
            'data' => $this->repository->store($request, $table, $id)
        ], 201);
    }

    /**
     * delete row with $id
     *
     * @param  Illuminate\Http\Request $request $request
     * @param  string $table
     * @param  string|int $id
     * @return Response
     */
    public function delete(Request $request, string $table, $id): Response {
        return response()->formatApi([
            $this->repository->delete($request, $table, $id)
        ], 204);
    }
}
