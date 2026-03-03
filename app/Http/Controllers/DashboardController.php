<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'activeMembership' => $user->activeMembership()->with('flatshare.owner')->first(),
            'ownedFlatshares' => $user->ownedFlatshares()->latest()->get(),
            'memberships' => $user->memberships()->with('flatshare')->latest()->get(),
        ]);
    }
}
