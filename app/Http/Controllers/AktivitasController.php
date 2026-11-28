<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        return view('aktivitas.index', [
            'aktivitas' => $rental->aktivitas()->with('user')->latest()->paginate(20),
        ]);
    }
}
