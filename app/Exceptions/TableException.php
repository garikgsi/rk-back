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
            return response()->json([
                'is_error' => true,
                'error' => $this->getMessage()
            ], $this->getCode);
        } else {
            return response()->view('errors.table',['error' => $this->getMessage(), 'code'=>$this->getCode()],$this->getCode());
        }
    }
}
