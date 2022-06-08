<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\Table;
use App\Services\TableRepositoryService;
use Illuminate\Http\Response;
use App\Exceptions\PermissionsException;

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
     * resourse class name for model
     */
    protected string|null $resourceClassName;

    /**
     * __construct
     *
     * @param  Request $request
     * @return void
     */
    public function __construct(TableRepositoryService $repository)
    {
        $this->repository = $repository;
        $this->resourceClassName = $repository->resourceClass();
    }

    /**
     * show rows list
     *
     * @param  mixed $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request): Response {
        $repositoryResponse = $this->repository->get($request);
        return response()->formatApi([
            'data' => $this->resourceClassName ? $this->resourceClassName::collection($repositoryResponse['data']) : $repositoryResponse['data']->toArray(),
            'count' => $repositoryResponse['count'],
        ]);
    }

    /**
     * show row identified by $id
     *
     * @param  string $table
     * @param  mixed $id
     * @return Illuminate\Http\Response
     */
    public function show(string $table, $id): Response {
        $repositoryResponse = $this->repository->find($table,(int)$id);
        return response()->formatApi([
            'data' => $this->resourceClassName ? new $this->resourceClassName($repositoryResponse) : $repositoryResponse->toArray(),
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
        $repositoryResponse = $this->repository->update($request, $table, (int)$id);
        return response()->formatApi([
            'data' => $this->resourceClassName ? new $this->resourceClassName($repositoryResponse) : $repositoryResponse->toArray(),
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
        $repositoryResponse = $this->repository->store($request, $table, $id);
        return response()->formatApi([
            'data' => $this->resourceClassName ? new $this->resourceClassName($repositoryResponse) : $repositoryResponse->toArray(),
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
