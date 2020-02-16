<?php

namespace App\Http\Controllers;

use App\Helper;
use Illuminate\Http\Request;
use Validator;

class SetupController extends Controller
{
    public function updateMissingConfigs(Request $request)
    {
        $fields_to_update = [
            'GOOGLE_RECAPTCHA_TOKEN' => $request->input('GOOGLE_RECAPTCHA_TOKEN'),
        ];

        Validator::extend('setup_pusher', function ($attribute, $value, $parameters, $validator) {
            return call_user_func_array(['Helper', 'arePusherConfigsValid'], $value);
        }, 'Pusher: It seems like these keys are invalid.');

        Validator::extend('setup_google_recaptcha', function ($attribute, $value, $parameters, $validator) {
            return Helper::isValidReCaptchaToken($parameters[0], $value);
        }, 'Google ReCaptcha: Site secret and site key didn\'t match.');

        Validator::extend('setup_url_prefix', function ($attribute, $value, $parameters, $validator) {
            return Helper::validateSystemUrlPrefix($value);
        }, 'URL prefix: It must either be empty or a URL path starting with /<mandatory prefix>/<maybe>/<more>');

        $validator_params = [
            'inputs' => [],
            'rules'  => [],
        ];

        foreach (Helper::getPendingDotEnvFileConfigs() as $group => $fields) {
            foreach ($fields as $category => $field) {
                $field_name = $field['name'];

                $is_checkbox = $field['type'] === 'checkbox';

                $fields_to_update[$field_name] = $is_checkbox ? false : '';

                $field_value = $request->input($field_name);

                if ($field_value !== null) {
                    $fields_to_update[$field_name] = $is_checkbox ? 'true' === $field_value : $field_value;
                }
            }
        }

        if (isset($fields_to_update['PUSHER_ENABLED']) && $fields_to_update['PUSHER_ENABLED'] !== false) {
            $values = [
                $fields_to_update['PUSHER_APP_KEY'],
                $fields_to_update['PUSHER_APP_ID'],
                $fields_to_update['PUSHER_APP_CLUSTER'],
                $fields_to_update['PUSHER_APP_SECRET'],
            ];

            $validator_params['inputs']['pusher'] = $values;
            $validator_params['rules']['pusher'] = 'setup_pusher';
        }

        if (isset($fields_to_update['GOOGLE_RECAPTCHA_ENABLED']) && $fields_to_update['GOOGLE_RECAPTCHA_ENABLED'] !== false) {
            $validator_params['inputs']['google-recaptcha'] = $fields_to_update['GOOGLE_RECAPTCHA_TOKEN'];
            $validator_params['rules']['google-recaptcha'] = 'setup_google_recaptcha:'.$fields_to_update['GOOGLE_RECAPTCHA_SITE_SECRET'];
        }

        if (isset($fields_to_update['LARAVEL_SURVEY_PREFIX_URL']) && !Helper::validateSystemUrlPrefix($fields_to_update['LARAVEL_SURVEY_PREFIX_URL'])) {
            $validator_params['inputs']['url_prefix'] = $fields_to_update['LARAVEL_SURVEY_PREFIX_URL'];
            $validator_params['rules']['url_prefix'] = 'setup_url_prefix';
        }

        $validator = Validator::make($validator_params['inputs'], $validator_params['rules']);

        if ($validator->fails()) {
            return redirect()->route('home')->withErrors($validator)->withInput();
        }

        Helper::updateDotEnvFileVars($fields_to_update);

        $request->session()->flash('success', 'Configurations updated successfully.');

        return redirect()->route('home')->withInput();
    }
}
