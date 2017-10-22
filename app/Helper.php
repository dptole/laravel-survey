<?php

namespace App;

use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class Helper {
  static $pusher = null;

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
}
