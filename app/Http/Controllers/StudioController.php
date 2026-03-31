<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StudioController extends Controller
{
    public function index(): View
    {
        return view('home');
    }
}
