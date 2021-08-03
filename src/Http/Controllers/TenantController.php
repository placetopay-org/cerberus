<?php

namespace Placetopay\Cerberus\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('clean-cache');
    }

    public function clean()
    {
        Artisan::call('cache:clear');

        return response()->json([
           'message' => 'cache cleared',
        ]);
    }
}
