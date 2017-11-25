<?php

namespace App;

use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class Helper {
  static $pusher = null;
  static $lsr = null;
  static $tzs = null;

  public static function getPusherOptions() {
    return [
      'cluster' => env('PUSHER_APP_CLUSTER'),
      'encrypted' => true
    ];
  }

  public static function openForm($route, array $route_arguments = [], array $form_arguments = []) {
    return Form::open(array_merge(
      [
        'autocomplete' => 'off'
      ],
      is_array($form_arguments) ? $form_arguments : [],
      [
        'url' => URL::to(
          self::urlRemoveDomain(route($route, $route_arguments)),
          [],
          isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'dptole.ngrok.io'
        )
      ]
    ));
  }

  public static function closeForm() {
    return Form::close();
  }

  public static function urlRemoveDomain($url) {
    return is_string($url) ? preg_replace('#^https?://[^/]+(.*)#', '$1', $url) : '';
  }

  public static function route($route) {
    $url = route($route);
    return self::isSecureRequest() ? preg_replace('#^http(://.*)$#', 'https$1', $url) : $url;
  }

  public static function isSecureRequest() {
    return Request::secure() || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']);
  }

  public static function isValidHTTPStatus($status) {
    return is_integer($status) && $status > 99 && $status < 600;
  }

  public static function isSuccessHTTPStatus($status) {
    return self::isValidHTTPStatus($status) && $status > 199 && $status < 300;
  }

  public static function generateRandomString($length = 10) {
    $string = '';
    $length = is_numeric($length) && $length > 0 && $length < 32 ? $length : 10;
    while(strlen($string) < $length)
      preg_match('/\w|[-$!@+=]/', chr(rand(32, 128)), $match) && (
        $string .= $match[0]
      );
    return $string;
  }

  public static function broadcast($channel, $event, $message) {
    if(!self::$pusher)
      self::$pusher = new \Pusher\Pusher(
        env('PUSHER_APP_KEY'),
        env('PUSHER_APP_SECRET'),
        env('PUSHER_APP_ID'),
        self::getPusherOptions()
      );

    self::$pusher->trigger($channel, $event, $message);
  }

  public static function isPositiveInteger($value) {
    return is_numeric($value) && $value >= 0 && ~~$value === $value;
  }

  public static function createCarbonRfc1123String($date) {
    return self::createCarbonDate($date)->toRfc1123String();
  }

  public static function createCarbonDiffForHumans($date) {
    return self::createCarbonDate($date)->diffForHumans();
  }

  public static function createCarbonDate($date) {
    return new Carbon($date); //, 'America/Sao_Paulo');
  }

  public static function loadLSR() {
    if(!self::$lsr)
      self::$lsr = json_decode(file_get_contents(getcwd() . '/../app/data/iana-language-subtag-registry.json'));
  }

  public static function loadTimezones() {
    // https://en.wikipedia.org/wiki/List_of_UTC_time_offsets
    if(!self::$tzs)
      self::$tzs = json_decode(file_get_contents(getcwd() . '/../app/data/list-of-utc-time-offset.json'));
  }

  public static function lsrGetLanguageRegions($accept_language) {
    self::loadLSR();

    $blocks = explode(',', $accept_language);

    $languages_regions = array_map(function($language_tag) {
      if(!preg_match('#([^-;]+)(?:-([^-]+))?#', $language_tag, $match))
        return;

      return [
        'language' => $match[1],
        'region' => isset($match[2]) ? $match[2] : ''
      ];
    }, $blocks);
    $languages_regions = array_filter($languages_regions);

    $region_by_language = array_reduce(Helper::$lsr->language, function($acc, $lr) use ($languages_regions) {
      if(count($languages_regions) === 0):
        return $acc;
      endif;

      foreach($languages_regions as $key => $language_region):
        if($lr->Subtag === $language_region['language']):
          if(!isset($acc[$lr->Description]) || !$acc[$lr->Description]):
            $acc[$lr->Description] = array_reduce(Helper::$lsr->region, function($acc, $reg) use ($language_region) {
              if($reg->Subtag === $language_region['region']):
                $acc = $reg->Description;
              endif;

              return $acc;
            }, '');
          endif;
          unset($languages_regions[$key]);
          break;
        endif;
      endforeach;

      return $acc;
    }, []);

    $lrs = [];
    foreach($region_by_language as $language => $region):
      $lrs []= $language;
      if($region):
        $lrs[count($lrs) - 1] .= "/$region";
      endif;
    endforeach;
    sort($lrs);

    return implode(', ', $lrs);
  }

  public static function tzGetCountries($tz_minutes, $opposite = true) {
    self::loadTimezones();

    if($opposite):
      $tz_minutes = -$tz_minutes;
    endif;

    $sign = $tz_minutes > 0 ? '+' : '-';
    $unsigned_tz_minutes = abs($tz_minutes);

    $tz_key = 'UTC' . $sign .
      str_pad($unsigned_tz_minutes / 60 | 0, 2, '0', STR_PAD_LEFT) .
      ':' .
      str_pad($unsigned_tz_minutes % 60, 2, '0', STR_PAD_LEFT)
    ;

    $countries = property_exists(self::$tzs, $tz_key) ?  self::$tzs->{$tz_key} : [];
    sort($countries);
    $countries = implode(', ', $countries);

    return $countries;
  }

  public static function getIpFromRequestInfo($request_info) {
    $has_x_forwarded_for = property_exists($request_info->headers, 'x-forwarded-for') &&
      is_array($request_info->headers->{'x-forwarded-for'}) &&
      count($request_info->headers->{'x-forwarded-for'}) === 1 &&
      is_string($request_info->headers->{'x-forwarded-for'}[0]) &&
      strlen(trim($request_info->headers->{'x-forwarded-for'}[0])) > 0
    ;

    if($has_x_forwarded_for):
      return trim($request_info->headers->{'x-forwarded-for'}[0]);
    endif;

    $has_ips = property_exists($request_info, 'ips') &&
      is_array($request_info->ips) &&
      count($request_info->ips) === 1 &&
      is_string($request_info->ips[0]) &&
      strlen(trim($request_info->ips[0])) > 0
    ;

    return $has_ips ? trim($request_info->ips[0]) : false;
  }

  public static function getDbIpUrlFromRequestInfo($request_info) {
    $ip = self::getIpFromRequestInfo($request_info);

    if($ip):
      return "https://db-ip.com/$ip";
    endif;

    return false;
  }
}
