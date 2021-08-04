<?php

namespace Placetopay\Cerberus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('clean-cache');
    }

    public function clean(Request $request)
    {
        Artisan::call("tenants:artisan cache:clear --tenant={$request->getHost()}");

        return response()->json([
           'message' => 'cache cleared',
        ]);
    }
}
