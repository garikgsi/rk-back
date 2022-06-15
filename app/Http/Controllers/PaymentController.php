<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Repositories\PaymentRepository;
use App\Http\Resources\PaymentResource;

class PaymentController extends Controller
{
    public function index(PaymentRepository $repository, Request $request)
    {
        $response = $repository->get($request);
        return response()->formatApi([
            'data' => PaymentResource::collection($response['data']),
            'count' => $response['count'],
        ]);
    }

    public function show(PaymentRepository $repository, Request $request, $id)
    {
        $payment = $repository->find($request, $id);
        if ($request->user()->can('view',$payment)) {
            return response()->formatApi([
                'data' => new PaymentResource($payment)
            ]);
        }
    }

}
