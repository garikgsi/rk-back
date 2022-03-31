<?php

namespace App\Http\Middleware;

use App\Exceptions\TableException;
use Closure;
use Illuminate\Http\Request;
use App\Facades\Table;

class TableValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // check existed table
        $repository = Table::use($request->table);

        // check permissions to method
        switch (strtolower($request->method())) {
            case 'get': {
                $method = ($request->has('id')) ? 'view' : 'viewAny';
            } break;
            case 'post': {
                $method = 'create';
            } break;
            case 'put': {
                $method = 'update';
            } break;
            case 'patch': {
                $method = 'update';
            } break;
            case 'delete': {
                $method = 'delete';
            } break;
        }
        // if (!$request->user()->can($method, $repository->getModel())) {
        //     throw new TableException('Недостаточно прав для выполнения операции', 403);
        // };

        // TODO
        // validate form data, if exists rules and data
        // $request->validate($repository->getModel()->rules($method), $repository->getModel()->messages($method));

        // validation comlete
        return $next($request);
    }
}
