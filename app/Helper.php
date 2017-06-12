<?php

namespace App;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\URL;

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
}
