<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * Send a success response.
     *
     * @param mixed $result
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function Success($data, $message, $code = 200)
    {
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public static function Error($data, $message, $code = 500)
    {
        return response()->json([
            'status' => 0,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public static function Validate($data, $message, $code = 422)
    {
        return response()->json([
            'status' => 0,
            'data' => $data,
            'message' => $message,
        ], $code);
    }}
