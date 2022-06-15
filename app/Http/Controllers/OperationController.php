<?php

namespace App\Http\Controllers;

use App\Http\Resources\OperationResource;
use App\Services\Repositories\OperationRepository;
use Illuminate\Http\Request;

class OperationController extends Controller
{
    public function index(OperationRepository $repository, Request $request)
    {
        $response = $repository->get($request);
        return response()->formatApi([
            'data' => OperationResource::collection($response['data']),
            'count' => $response['count'],
        ]);
    }

    public function show(OperationRepository $repository, Request $request, $id)
    {
        $operation = $repository->find($request, $id);
        if ($request->user()->can('view',$operation)) {
            return response()->formatApi([
                'data' => new OperationResource($operation)
            ]);
        }
    }
}
