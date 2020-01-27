<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surveys;

class HomeController extends Controller {
  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {
    return view('home')->with([
      'available_surveys' => Surveys::getAvailables()
    ]);
  }

  /**
   * Go to the main page.
   *
   * @return \Illuminate\Http\Response
   */
  public function root() {
    if(env('LARAVEL_SURVEY_PREFIX_URL') === '/'):
      return $this->index();
    else:
      return redirect(env('LARAVEL_SURVEY_PREFIX_URL'));
    endif;
  }
}
