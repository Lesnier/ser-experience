<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LandingPage;

class LandingController extends Controller
{
    public function index()
    {
        $landing = LandingPage::where('is_active', true)->first();

        if (!$landing) {
            return view('welcome'); // Fallback if no landing page is active
        }

        return view('landing.index', compact('landing'));
    }
}
