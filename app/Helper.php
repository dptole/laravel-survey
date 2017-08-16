<?php

namespace App;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;

class Helper {
  public static function openForm($route, array $route_arguments = [], array $form_arguments = []) {
    return Form::open(array_merge(
      is_array($form_arguments) ? $form_arguments : [],
      [
        'url' => URL::to(
          Helper::urlRemoveDomain(route($route, $route_arguments)),
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
    return Helper::isSecureRequest() ? preg_replace('#^http(://.*)$#', 'https$1', $url) : $url;
  }

  public static function isSecureRequest() {
    return Request::secure() || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']);
  }

  public static function isValidHTTPStatus($status) {
    return is_integer($status) && $status > 99 && $status < 600;
  }

  public static function isSuccessHTTPStatus($status) {
    return Helper::isValidHTTPStatus($status) && $status > 199 && $status < 300;
  }
}
