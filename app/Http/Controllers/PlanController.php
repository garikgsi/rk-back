<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use App\Services\Repositories\PlanRepository;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(PlanRepository $repository, Request $request)
    {
        $response = $repository->get($request);
        return response()->formatApi([
            'data' => PlanResource::collection($response['data']),
            'count' => $response['count'],
        ]);
    }

    public function show(PlanRepository $repository, Request $request, $id)
    {
        return response()->formatApi([
            'data' => new PlanResource($repository->find($request, $id))
        ]);
    }}
