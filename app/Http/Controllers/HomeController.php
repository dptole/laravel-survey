<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Surveys;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home')->with([
            'available_surveys' => Surveys::getAvailables(),
        ]);
    }

    /**
     * Go to the main page.
     *
     * @return \Illuminate\Http\Response
     */
    public function root()
    {
        if (Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL') === '') {
            return $this->index();
        } else {
            return redirect(Helper::getDotEnvFileVar('LARAVEL_SURVEY_PREFIX_URL'));
        }
    }
}
