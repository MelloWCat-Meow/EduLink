<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Placeholder home for verified tutors until the tutor dashboard is built.
 */
class AreaTutorController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tutor = $request->user()->tutor;

        return view('tutor.area', ['tutor' => $tutor, 'tampilDiPencarian' => $tutor->isListed()]);
    }
}
