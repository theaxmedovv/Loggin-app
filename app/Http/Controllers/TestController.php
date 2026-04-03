<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function index()
    {
        Log::info('Test log ishladi');

        return response('OK', 200);
    }
}
