<?php

namespace Placetopay\Cerberus\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Landlord;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('clean-cache');
    }

    public function clean(Request $request): JsonResponse
    {
        Landlord::execute(function () use ($request) {
            $host = $request->getHost();

            if (Cache::has("tenant_{$host}")) {
                Cache::forget("tenant_{$host}");
            }
        });

        return response()->json([
            'message' => 'cache cleared',
        ]);
    }
}
