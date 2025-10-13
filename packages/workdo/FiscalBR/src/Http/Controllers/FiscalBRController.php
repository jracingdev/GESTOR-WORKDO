<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FiscalBRController extends Controller
{
    /**
     * Display the fiscal dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('fiscalbr::dashboard');
    }
}

