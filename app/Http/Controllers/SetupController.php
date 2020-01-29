<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper;
use ReCaptcha\ReCaptcha;
use Validator;

class SetupController extends Controller {
  public function updateMissingConfigs(Request $request) {
    $fields_to_update = [
      'GOOGLE_RECAPTCHA_TOKEN' => $request->input('GOOGLE_RECAPTCHA_TOKEN')
    ];

    Validator::extend('setup_pusher', function($attribute, $value, $parameters, $validator) {
      list($app_key, $app_id, $app_cluster, $app_secret) = $value;
      return Helper::arePusherConfigsValid($app_key, $app_id, $app_cluster, $app_secret);
    }, 'Pusher: It seems like these keys are invalid.');

    Validator::extend('setup_google_recaptcha', function($attribute, $value, $parameters, $validator) {
      list($site_secret) = $parameters;
      return (new ReCaptcha($site_secret))->verify($value)->isSuccess();
    }, 'Google ReCaptcha: Site secret and site key didn\'t match.');

    Validator::extend('setup_url_prefix', function($attribute, $value, $parameters, $validator) {
      $trimmed_value = trim($value);
      $exploded_value = explode('/', $trimmed_value);
      $exploded_index = 0;

      $all_url_validated = array_reduce($exploded_value, function($acc, $path) use (&$exploded_index) {
        if(!$acc) return $acc;

        if($exploded_index === 0):
          $acc = $path === '';
        else:
          $trimmed_path = trim($path);
          $acc = $trimmed_path === $path && strlen($path) > 0;
        endif;

        $exploded_index++;
        return $acc;
      }, true);

      return $value === '' || (
        $value === $trimmed_value &&
        count($exploded_value) > 1 &&
        $all_url_validated
      );
    }, 'URL prefix: It must either be empty or a URL path starting with /<mandatory prefix>/<maybe>/<more>');

    $validator_params = [
      'inputs' => [],
      'rules' => []
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

    if($fields_to_update['PUSHER_ENABLED'] !== false):
      $values = [
        $fields_to_update['PUSHER_APP_KEY'],
        $fields_to_update['PUSHER_APP_ID'],
        $fields_to_update['PUSHER_APP_CLUSTER'],
        $fields_to_update['PUSHER_APP_SECRET']
      ];

      $validator_params['inputs']['pusher'] = $values;
      $validator_params['rules']['pusher'] = 'setup_pusher';
    endif;

    if($fields_to_update['GOOGLE_RECAPTCHA_ENABLED'] !== false):
      $validator_params['inputs']['google-recaptcha'] = $fields_to_update['GOOGLE_RECAPTCHA_TOKEN'];
      $validator_params['rules']['google-recaptcha'] = 'setup_google_recaptcha:' . $fields_to_update['GOOGLE_RECAPTCHA_SITE_SECRET'];
    endif;

    $validator_params['inputs']['url_prefix'] = $fields_to_update['LARAVEL_SURVEY_PREFIX_URL'];
    $validator_params['rules']['url_prefix'] = 'setup_url_prefix';

    $validator = Validator::make(
      $validator_params['inputs'],
      $validator_params['rules']
    );

    if($validator->fails()):
      return redirect()->route('home')->withErrors($validator)->withInput();
    endif;

    // @TODO
    // Update configurations in the .env file
    // Restart the server

    $request->session()->flash('success', 'Configurations updated successfully.');

    return redirect()->route('home')->withInput();
  }
}
