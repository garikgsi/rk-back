<?php

namespace App\Exceptions;

use Exception;
/**
 * logical exception table classes loading
 *
 */
class TableException extends Exception
{
    public function render($request) {
        if ($request->expectsJson()) {
            return response()->formatApi([
                'error' => $this->getMessage()
            ], $this->getCode());

        } else {
            return response()->view('errors.table',['error' => $this->getMessage(), 'code'=>$this->getCode()],(int)$this->getCode());
        }
    }
}
