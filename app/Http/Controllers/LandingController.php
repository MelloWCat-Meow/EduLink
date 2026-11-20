<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function __invoke(): View
    {
        return view('landing', [
            'levels' => config('landing.levels'),
            'subjects' => config('landing.subjects'),
            'locations' => config('landing.locations'),
            'sampleTutors' => config('landing.sample_tutors'),
            'steps' => config('landing.steps'),
            'faqs' => config('landing.faqs'),
            'contact' => config('landing.contact'),
        ]);
    }
}
