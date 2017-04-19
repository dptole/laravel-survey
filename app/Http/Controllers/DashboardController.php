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
    $surveys = Surveys::where('user_id', '=', $request->user()->id)
      ->orderBy('updated_at', 'desc')
      ->paginate(10)
    ;
    return view('dashboard')->withSurveys($surveys);
  }
}

