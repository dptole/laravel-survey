<?php

namespace App;

use Carbon\Carbon;
use Collective\Html\FormFacade as Form;
use Collective\Html\HtmlFacade as Html;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Pusher\Pusher;
use ReCaptcha\ReCaptcha;

class Helper
{
    public static $pusher = null;
    public static $lsr = null;
    public static $tzs = null;
    public static $db_ip_html_ips_info = [];
    public static $dot_env_file = null;

    public static function getPusherOptions()
    {
        return [
            'cluster'   => self::getDotEnvFileVar('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
        ];
    }

    public static function linkRoute($route, $text_content, $route_args = [], $html_attrs = [])
    {
        $html_string = Html::linkRoute($route, $text_content, $route_args, $html_attrs);

        if (self::isSecureRequest()) {
            $html_string = str_replace('href="http:', 'href="https:', $html_string);
        }

        return new HtmlString($html_string);
    }

    public static function openForm($route, $route_arguments = [], $form_arguments = [])
    {
        return Form::open(array_merge(
      [
          'autocomplete' => 'off',
          'name'         => $route,
      ],
      is_array($form_arguments) ? $form_arguments : [],
      [
          'url' => URL::to(
            self::urlRemoveDomain(route($route, $route_arguments)),
            [],
            self::isSecureRequest()
          ),
      ]
    ));
    }

    public static function closeForm()
    {
        return Form::close();
    }

    public static function urlRemoveDomain($url)
    {
        return is_string($url) ? preg_replace('#^https?://[^/]+(.*)#', '$1', $url) : '';
    }

    public static function route($route)
    {
        $url = route($route);

        return self::isSecureRequest() ? preg_replace('#^http(://.*)$#', 'https$1', $url) : $url;
    }

    public static function isSecureRequest()
    {
        $secure_by_header = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'];

        $mocked_secure_by_header = self::getTestEnvMockVar('isSecureRequest', $secure_by_header);

        return Request::secure() || $mocked_secure_by_header;
    }

    public static function isGoogleReCaptchaEnabled()
    {
        $recaptcha_enabled_by_env = self::getDotEnvFileVar('GOOGLE_RECAPTCHA_ENABLED') === 'true';

        return self::getTestEnvMockVar('isGoogleReCaptchaEnabled', $recaptcha_enabled_by_env);
    }

    public static function isPusherEnabled()
    {
        return self::getDotEnvFileVar('PUSHER_ENABLED') === 'true';
    }

    public static function isValidHTTPStatus($status)
    {
        return is_int($status) && $status > 99 && $status < 600;
    }

    public static function isSuccessHTTPStatus($status)
    {
        return self::isValidHTTPStatus($status) && $status > 199 && $status < 300;
    }

    public static function generateRandomString($length = 10)
    {
        $string = '';
        $length = is_numeric($length) && $length > 0 && $length < 32 ? $length : 10;
        while (strlen($string) < $length) {
            preg_match('/\w|[-$!@+=]/', chr(rand(32, 128)), $match) && (
        $string .= $match[0]
      );
        }

        return $string;
    }

    public static function broadcast($channel, $event, $message)
    {
        if (self::isPusherEnabled()) {
            if (!self::$pusher) {
                self::$pusher = new Pusher(
          self::getDotEnvFileVar('PUSHER_APP_KEY'),
          self::getDotEnvFileVar('PUSHER_APP_SECRET'),
          self::getDotEnvFileVar('PUSHER_APP_ID'),
          self::getPusherOptions()
        );
            }

            self::$pusher->trigger($channel, $event, $message);
        } else {
            Sse::trigger($channel, $event, $message);
        }
    }

    public static function isPositiveInteger($value)
    {
        return is_numeric($value) && $value >= 0 && ~~$value === $value;
    }

    public static function createCarbonRfc1123String($date)
    {
        return self::createCarbonDate($date)->toRfc1123String();
    }

    public static function createCarbonDiffForHumans($date)
    {
        return self::createCarbonDate($date)->diffForHumans();
    }

    public static function createCarbonDate($date)
    {
        return new Carbon($date); //, 'America/Sao_Paulo');
    }

    public static function loadLSR()
    {
        if (!self::$lsr) {
            self::$lsr = json_decode(file_get_contents(__DIR__.'/../app/data/iana-language-subtag-registry.json'));
        }
    }

    public static function loadTimezones()
    {
        // https://en.wikipedia.org/wiki/List_of_UTC_time_offsets
        if (!self::$tzs) {
            self::$tzs = json_decode(file_get_contents(__DIR__.'/../app/data/list-of-utc-time-offset.json'));
        }
    }

    public static function lsrGetLanguageRegions($accept_language)
    {
        self::loadLSR();

        $blocks = explode(',', $accept_language);

        $languages_regions = array_map(function ($language_tag) {
            if (!preg_match('#([^-;]+)(?:-([^-]+))?#', $language_tag, $match)) {
                return;
            }

            return [
                'language' => $match[1],
                'region'   => isset($match[2]) ? $match[2] : '',
            ];
        }, $blocks);
        $languages_regions = array_filter($languages_regions);

        $region_by_language = array_reduce(self::$lsr->language, function ($acc, $lr) use ($languages_regions) {
            if (count($languages_regions) === 0) {
                return $acc;
            }

            foreach ($languages_regions as $key => $language_region) {
                if ($lr->Subtag === $language_region['language']) {
                    if (!isset($acc[$lr->Description]) || !$acc[$lr->Description]) {
                        $acc[$lr->Description] = array_reduce(self::$lsr->region, function ($acc, $reg) use ($language_region) {
                            if ($reg->Subtag === $language_region['region']) {
                                $acc = $reg->Description;
                            }

                            return $acc;
                        }, '');
                    }
                    unset($languages_regions[$key]);
                    break;
                }
            }

            return $acc;
        }, []);

        $lrs = [];
        foreach ($region_by_language as $language => $region) {
            $lrs[] = $language;
            if ($region) {
                $lrs[count($lrs) - 1] .= "/$region";
            }
        }
        sort($lrs);

        return implode(', ', $lrs);
    }

    public static function tzGetCountries($tz_minutes, $opposite = true)
    {
        self::loadTimezones();

        if ($opposite) {
            $tz_minutes = -$tz_minutes;
        }

        $sign = $tz_minutes >= 0 ? '+' : '-';
        $unsigned_tz_minutes = abs($tz_minutes);

        $tz_key = 'UTC'.$sign.
      str_pad($unsigned_tz_minutes / 60 | 0, 2, '0', STR_PAD_LEFT).
      ':'.
      str_pad($unsigned_tz_minutes % 60, 2, '0', STR_PAD_LEFT);

        $countries = property_exists(self::$tzs, $tz_key) ? self::$tzs->{$tz_key} : [];
        sort($countries);
        $countries = implode(', ', $countries);

        return $countries;
    }

    public static function getIpFromRequestInfo($request_info)
    {
        $has_x_forwarded_for = property_exists($request_info->headers, 'x-forwarded-for') &&
      is_array($request_info->headers->{'x-forwarded-for'}) &&
      count($request_info->headers->{'x-forwarded-for'}) === 1 &&
      is_string($request_info->headers->{'x-forwarded-for'}[0]) &&
      strlen(trim($request_info->headers->{'x-forwarded-for'}[0])) > 0;

        if ($has_x_forwarded_for) {
            return trim($request_info->headers->{'x-forwarded-for'}[0]);
        }

        $has_ips = property_exists($request_info, 'ips') &&
      is_array($request_info->ips) &&
      count($request_info->ips) === 1 &&
      is_string($request_info->ips[0]) &&
      strlen(trim($request_info->ips[0])) > 0;

        return $has_ips ? trim($request_info->ips[0]) : false;
    }

    public static function getDbIpUrlFromRequestInfo($request_info)
    {
        $ip = self::getIpFromRequestInfo($request_info);

        if ($ip) {
            return "https://db-ip.com/$ip";
        }

        return false;
    }

    public static function isTestingEnv()
    {
        return env('APP_ENV') === 'testing';
    }

    public static function getTestEnvMockVar($var_name, $fallback)
    {
        if (self::isTestingEnv() && isset($GLOBALS[$var_name])) {
            return $GLOBALS[$var_name];
        }

        return $fallback;
    }

    public static function getRequestIp()
    {
        $headers = self::getTestEnvMockVar('getRequestIp_headers', getallheaders());

        if (is_array($headers) && isset($headers['x-forwarded-for'])) {
            return $headers['x-forwarded-for'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return false;
    }

    public static function dbIpDecorateResponse($ip_info, $ip)
    {
        if (!is_object($ip_info)) {
            return $ip_info;
        }
        $ip_info->Ip = $ip;

        return $ip_info;
    }

    public static function dbIpGetIpInfo($ip)
    {
        $ip_from_html = self::dbIpGetIpInfoFromHtml($ip);

        if ($ip_from_html) {
            return self::dbIpDecorateResponse($ip_from_html, $ip);
        }

        return false;
    }

    public static function dbIpGetIpInfoFromHtml($ip)
    {
        if (isset(self::$db_ip_html_ips_info[$ip])) {
            return self::$db_ip_html_ips_info[$ip];
        }

        $start_time = -self::ms();
        $content = @file_get_contents("http://db-ip.com/$ip");
        $dom = new \DOMDocument();
        @$dom->loadHTML($content);
        $props = [];

        $tables = $dom->getElementsByTagName('table');

        foreach ($tables as $table_index => $table) {
            $trs = $table->getElementsByTagName('tr');

            if ($table_index === 1) {
                foreach ($trs as $tr_index => $tr) {
                    $th = $tr->getElementsByTagName('th');
                    $td = $tr->getElementsByTagName('td');

                    if ($tr_index === 0) {
                        if (!(
              $th->length === 3 &&
              $td->length === 0 &&
              $th->item(0)->textContent === 'Crawler' &&
              $th->item(1)->textContent === 'Proxy' &&
              $th->item(2)->textContent === 'Attack source'
            )) {
                            break;
                        }
                    } else {
                        $props['Security / Crawler'] = self::htmlTrim($td->item(0)->textContent);
                        $props['Security / Proxy'] = self::htmlTrim($td->item(1)->textContent);
                        $props['Security / Attack source'] = self::htmlTrim($td->item(2)->textContent);
                    }
                }
            } else {
                foreach ($trs as $tr_index => $tr) {
                    $th = $tr->getElementsByTagName('th');
                    $td = $tr->getElementsByTagName('td');

                    $text_th = self::htmlTrim($th->item(0)->textContent);
                    $text_td = self::htmlTrim($td->item(0)->textContent);

                    if ($text_th && $text_td) {
                        $props[$text_th] = $text_td;
                    }
                }
            }
        }

        if (count($props) < 1) {
            return false;
        }

        $ip_info = new \stdClass();

        foreach ($props as $key => $value) {
            $ip_info->$key = $value;
        }

        $ip_info->Elapsed = $start_time + self::ms();

        return self::$db_ip_html_ips_info[$ip] = self::dbIpDecorateResponse($ip_info, $ip);
    }

    public static function htmlTrim($data)
    {
        return preg_replace('#^[\x09\xa0\xc2]*|[\x09\xa0\xc2]*$#', '', trim($data));
    }

    public static function ms()
    {
        return microtime(true) * 1000 | 0;
    }

    public static function getGoogleReCaptchaApiAsset()
    {
        echo '<script async src="https://www.google.com/recaptcha/api.js"></script>';
    }

    public static function getDotEnvFilePath()
    {
        return dirname(__DIR__).'/.env';
    }

    public static function getDotEnvFileVar($name)
    {
        $dot_env = self::getDotEnvFile();

        if (isset($dot_env[$name])) {
            return $dot_env[$name];
        }

        return null;
    }

    public static function getDotEnvFile()
    {
        if (self::$dot_env_file) {
            return self::$dot_env_file;
        }

        return self::$dot_env_file = parse_ini_file(
      self::getDotEnvFilePath(),
      false,
      INI_SCANNER_RAW
    );
    }

    public static function getDotEnvFileRaw()
    {
        return file_get_contents(
      self::getDotEnvFilePath()
    );
    }

    public static function writeDotEnvFileRaw($content)
    {
        self::$dot_env_file = null;

        return file_put_contents(
      self::getDotEnvFilePath(),
      $content
    );
    }

    public static function updateDotEnvFileVars($vars)
    {
        $content = self::getDotEnvFileRaw();

        foreach ($vars as $key => $value) {
            $value = is_bool($value) ? ($value === true ? 'true' : 'false') : $value;
            $content = preg_replace('/('.$key.')=.*/', '$1='.$value, $content);
        }

        return self::writeDotEnvFileRaw($content);
    }

    public static function isValidReCaptchaToken($site_secret, $token)
    {
        return (new ReCaptcha($site_secret))->verify($token)->isSuccess();
    }

    public static function validateSystemUrlPrefix($value)
    {
        $trimmed_value = trim($value);
        $exploded_value = explode('/', $trimmed_value);
        $exploded_index = 0;

        $all_url_validated = array_reduce($exploded_value, function ($acc, $path) use (&$exploded_index) {
            if (!$acc) {
                return $acc;
            }

            if ($exploded_index === 0) {
                $acc = $path === '';
            } else {
                $trimmed_path = trim($path);
                $acc = $trimmed_path === $path && strlen($path) > 0;
            }

            $exploded_index++;

            return $acc;
        }, true);

        return $value === '' || (
      $value === $trimmed_value &&
      count($exploded_value) > 1 &&
      $all_url_validated
    );
    }

    public static function getPendingDotEnvFileConfigs()
    {
        $pending = [];

        $envs = self::getDotEnvFile();

        if (!self::arePusherConfigsPresent($envs)) {
            $pending['Pusher'] = [
                'Enabled?' => [
                    'type'        => 'checkbox',
                    'description' => 'If disabled the system will use <a href="https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events" target="_blank">SSE</a> instead.',
                    'value'       => $envs['PUSHER_ENABLED'],
                    'name'        => 'PUSHER_ENABLED',
                ],
                'App id' => [
                    'type'        => 'text',
                    'description' => '',
                    'value'       => $envs['PUSHER_APP_ID'],
                    'name'        => 'PUSHER_APP_ID',
                ],
                'Key' => [
                    'type'        => 'text',
                    'description' => '',
                    'value'       => $envs['PUSHER_APP_KEY'],
                    'name'        => 'PUSHER_APP_KEY',
                ],
                'Secret' => [
                    'type'        => 'text',
                    'description' => '',
                    'value'       => $envs['PUSHER_APP_SECRET'],
                    'name'        => 'PUSHER_APP_SECRET',
                ],
                'Cluster' => [
                    'type'        => 'text',
                    'description' => '',
                    'value'       => $envs['PUSHER_APP_CLUSTER'],
                    'name'        => 'PUSHER_APP_CLUSTER',
                ],
            ];
        }

        if (!self::areGoogleReCaptchaConfigsPresent($envs)) {
            $pending['Google ReCaptcha'] = [
                'Enabled?' => [
                    'type'        => 'checkbox',
                    'description' => 'If disabled the system will not check if users are (probably) humans or bots.',
                    'value'       => $envs['GOOGLE_RECAPTCHA_ENABLED'],
                    'name'        => 'GOOGLE_RECAPTCHA_ENABLED',
                ],
                'Secret key' => [
                    'type'        => 'text',
                    'description' => '',
                    'value'       => $envs['GOOGLE_RECAPTCHA_SITE_SECRET'],
                    'name'        => 'GOOGLE_RECAPTCHA_SITE_SECRET',
                ],
                'Site key' => [
                    'type'        => 'text',
                    'description' => '',
                    'value'       => $envs['GOOGLE_RECAPTCHA_SITE_KEY'],
                    'name'        => 'GOOGLE_RECAPTCHA_SITE_KEY',
                ],
                'ReCaptcha' => [
                    'type'        => 'div',
                    'description' => 'A ReCaptcha element will appear, in case you wish to submit its configs, for validation purposes.',
                    'value'       => '',
                    'name'        => 'GOOGLE_RECAPTCHA_ELEMENT',
                ],
            ];
        }

        if (!self::validateSystemUrlPrefix($envs['LARAVEL_SURVEY_PREFIX_URL'])) {
            $pending['Site'] = [
                'URL prefix' => [
                    'type'        => 'text',
                    'description' => 'Leave it empty for the system to look like a standalone app. A new URL prefix must start with / but not end with this character.',
                    'value'       => $envs['LARAVEL_SURVEY_PREFIX_URL'],
                    'name'        => 'LARAVEL_SURVEY_PREFIX_URL',
                ],
            ];
        }

        return $pending;
    }

    public static function hasPendingDotEnvFileConfigs()
    {
        return count(self::getPendingDotEnvFileConfigs()) > 0;
    }

    public static function areGoogleReCaptchaConfigsPresent($dot_env_file_configs)
    {
        if ($dot_env_file_configs['GOOGLE_RECAPTCHA_ENABLED'] !== 'true') {
            return true;
        }

        $is_site_secret_present = isset($dot_env_file_configs['GOOGLE_RECAPTCHA_SITE_SECRET']) && !empty($dot_env_file_configs['GOOGLE_RECAPTCHA_SITE_SECRET']);
        $is_site_key = isset($dot_env_file_configs['GOOGLE_RECAPTCHA_SITE_KEY']) && !empty($dot_env_file_configs['GOOGLE_RECAPTCHA_SITE_KEY']);

        return $is_site_secret_present && $is_site_key;
    }

    public static function arePusherConfigsPresent($dot_env_file_configs)
    {
        if ($dot_env_file_configs['PUSHER_ENABLED'] !== 'true') {
            return true;
        }

        $is_app_id_present = isset($dot_env_file_configs['PUSHER_APP_ID']) && !empty($dot_env_file_configs['PUSHER_APP_ID']);
        $is_app_key_present = isset($dot_env_file_configs['PUSHER_APP_KEY']) && !empty($dot_env_file_configs['PUSHER_APP_KEY']);
        $is_app_secret_present = isset($dot_env_file_configs['PUSHER_APP_SECRET']) && !empty($dot_env_file_configs['PUSHER_APP_SECRET']);
        $is_app_cluster_present = isset($dot_env_file_configs['PUSHER_APP_CLUSTER']) && !empty($dot_env_file_configs['PUSHER_APP_CLUSTER']);

        return $is_app_id_present && $is_app_key_present && $is_app_secret_present && $is_app_cluster_present;
    }

    public static function arePusherConfigsValid($auth_key, $app_id, $cluster, $secret)
    {
        /*
          https://pusher.com/docs/channels/library_auth_reference/rest-api

          It was pretty annoying trying to implement a simple check using Pusher
          but thanks to their good API error responses I could succeed
        */

        $auth_timestamp = time();
        $auth_version = '1.0';
        $querystring = implode('&', [
            'auth_key='.$auth_key,
            'auth_timestamp='.$auth_timestamp,
            'auth_version='.$auth_version,
        ]);
        $path = '/apps/'.$app_id.'/channels';
        $method = 'GET';
        $signature_payload = implode("\n", [$method, $path, $querystring]);
        $signature = hash_hmac('sha256', $signature_payload, $secret);
        $querystring .= '&auth_signature='.$signature;
        $api = 'http://api-'.$cluster.'.pusher.com'.$path.'?'.$querystring;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $json = json_decode($response);

        curl_close($ch);

        return $json instanceof \stdClass &&
      property_exists($json, 'channels') &&
      $http_code === 200;
    }

    public static function isMaxMindGeoIpEnabled()
    {
        return isset($_SERVER['MM_IP_COUNTRY_CODE']) ||
      isset($_SERVER['MM_IP_EN_COUNTRY_NAME']) ||
      isset($_SERVER['MM_HEADER_COUNTRY_CODE']) ||
      isset($_SERVER['MM_HEADER_EN_COUNTRY_NAME']) ||

      isset($_SERVER['MM_IP_ASN_CODE']) ||
      isset($_SERVER['MM_IP_ASN_NAME']) ||
      isset($_SERVER['MM_HEADER_ASN_CODE']) ||
      isset($_SERVER['MM_HEADER_ASN_NAME']) ||

      isset($_SERVER['MM_IP_EN_CITY_NAME']) ||
      isset($_SERVER['MM_HEADER_EN_CITY_NAME']);
    }
}
