<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class BellyController extends Controller
{
    public function __invoke(): View
    {
        return view('belly');
    }
}
