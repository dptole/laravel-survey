<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper;

class SetupController extends Controller {
  public function updateMissingConfigs(Request $request) {
    $fields_to_update = [];

    foreach(Helper::getPendingDotEnvFileConfigs() as $group => $fields):
      foreach($fields as $category => $field):
        $field_name = $field['name'];

        $is_checkbox = $field['type'] === 'checkbox';

        $fields_to_update[$field_name] = $is_checkbox ? false : '';

        $field_value = $request->input($field_name);

        if($field_value !== null):
          $fields_to_update[$field_name] = $is_checkbox
            ? 'true' === $field_value
            : $field_value
          ;
        endif;
      endforeach;
    endforeach;

    /*
    https://stackoverflow.com/questions/46541323/laravel-form-array-validation

    array(9) {
      ["PUSHER_ENABLED"]=>
      bool(true)
      ["PUSHER_APP_ID"]=>
      string(0) ""
      ["PUSHER_APP_KEY"]=>
      string(0) ""
      ["PUSHER_APP_SECRET"]=>
      string(0) ""
      ["PUSHER_APP_CLUSTER"]=>
      string(0) ""
      ["GOOGLE_RECAPTCHA_ENABLED"]=>
      bool(true)
      ["GOOGLE_RECAPTCHA_SITE_SECRET"]=>
      string(0) ""
      ["GOOGLE_RECAPTCHA_SITE_KEY"]=>
      string(0) ""
      ["LARAVEL_SURVEY_PREFIX_URL"]=>
      string(8) "/laravel"
    }
    */

    return redirect()->route('home');
  }
}
