<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Repositories\KidParentRepository;
use App\Http\Resources\KidParentResource;

class KidParentController extends Controller
{
    public function index(KidParentRepository $repository, Request $request)
    {
        $response = $repository->get($request);
        return response()->formatApi([
            'data' => KidParentResource::collection($response['data']),
            'count' => $response['count'],
        ]);
    }
    public function show(KidParentRepository $repository, Request $request, $id)
    {
        return response()->formatApi([
            'data' => new KidParentResource($repository->find($request, $id))
        ]);
    }

}
