<?php

namespace App\Http\Controllers;

use App\Surveys;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * List the surveys.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDashboard(Request $request)
    {
        return view('dashboard')->withSurveys(Surveys::getAllByOwner($request->user()->id));
    }
}
