<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUiPlaygroundController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->role === 'admin', 403);

        return view('admin.ui.index');
    }
}
