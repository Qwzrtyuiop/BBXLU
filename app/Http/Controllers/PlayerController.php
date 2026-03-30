<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class PlayerController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard', ['panel' => 'players']);
    }
}
