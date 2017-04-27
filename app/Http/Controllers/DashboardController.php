<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surveys;

class DashboardController extends Controller {
  /**
   * List the surveys.
   *
   * @return \Illuminate\Http\Response
   */
  public function getDashboard(Request $request) {
    return view('dashboard')->withSurveys(Surveys::getAllByOwner($request->user()->id));
  }
}

