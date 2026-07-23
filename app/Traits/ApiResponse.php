<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, $message = null, $status = 200, array $extra = [])
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra), $status);
    }
}
