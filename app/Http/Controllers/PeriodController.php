<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Repositories\PeriodRepository;
use App\Http\Resources\PeriodResource;

class PeriodController extends Controller
{
    public function index(PeriodRepository $repository, Request $request)
    {
        $response = $repository->get($request);
        return response()->formatApi([
            'data' => PeriodResource::collection($response['data']),
            'count' => $response['count'],
        ]);
    }


    public function show(PeriodRepository $repository, Request $request, $id)
    {
        $period = $repository->find($request, $id);
        if ($request->user()->can('view',$period)) {
            return response()->formatApi([
                'data' => new PeriodResource($period)
            ]);
        }
    }

}
