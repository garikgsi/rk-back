<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrganizationResource;
use App\Services\Repositories\OrganizationRepository;
use Illuminate\Http\Request;


class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(OrganizationRepository $repository, Request $request)
    {
        $response = $repository->get($request);
        return response()->formatApi([
            'data' => OrganizationResource::collection($response['data']),
            'count' => $response['count'],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(OrganizationRepository $repository, Request $request, $id)
    {
        return response()->formatApi([
            'data' => new OrganizationResource($repository->find($request, $id))
        ]);
    }
}
