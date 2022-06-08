<?php

namespace App\Http\Controllers;

use App\Services\Repositories\KidRepository;
use Illuminate\Http\Request;
use App\Http\Resources\KidResource;

class KidController extends Controller
{
    public function index(KidRepository $repository, Request $request)
    {
        $response = $repository->get($request);
        return response()->formatApi([
            'data' => KidResource::collection($response['data']),
            'count' => $response['count'],
        ]);
    }

    public function show(KidRepository $repository, Request $request, $id)
    {
        return response()->formatApi([
            'data' => new KidResource($repository->find($request, $id))
        ]);
    }

}
