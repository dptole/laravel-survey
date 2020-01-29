<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper;
use ReCaptcha\ReCaptcha;

class SetupController extends Controller {
  public function updateMissingConfigs(Request $request) {
    $fields_to_update = [
      'GOOGLE_RECAPTCHA_TOKEN' => $request->input('GOOGLE_RECAPTCHA_TOKEN')
    ];

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

    $setup_errors = [];

    if($fields_to_update['PUSHER_ENABLED'] !== false):
      // @TODO
      // Transform this check into a validator
      // This way the user can be notified of different kinds of errors

      $app_key = $fields_to_update['PUSHER_APP_KEY'];
      $app_secret = $fields_to_update['PUSHER_APP_SECRET'];
      $app_id = $fields_to_update['PUSHER_APP_ID'];
      $app_cluster = $fields_to_update['PUSHER_APP_CLUSTER'];

      if(!Helper::arePusherConfigsValid($app_key, $app_id, $app_cluster, $app_secret)):
        $setup_errors['Pusher'] = 'It seems like these keys are invalid.';
      endif;
    endif;

    if($fields_to_update['GOOGLE_RECAPTCHA_ENABLED'] !== false):
      // @TODO
      // Transform this check into a validator
      // This way the user can be notified of different kinds of errors

      $rc = new ReCaptcha(
        $fields_to_update['GOOGLE_RECAPTCHA_SITE_SECRET']
      );

      if(!$rc->verify($fields_to_update['GOOGLE_RECAPTCHA_TOKEN'])->isSuccess()):
        $setup_errors['Google ReCaptcha'] = 'It seems like these keys are invalid.';
      endif;
    endif;

    // @TODO
    // In case all fields are valid
    // Update the .env file
    // And redirect home without params

    return redirect()->route('home')->with([
      'setup_errors' => $setup_errors,
      'last_inputs' => $fields_to_update
    ]);
  }
}
