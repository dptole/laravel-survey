<?php

namespace App;
use ReflectionClass;
use JsonSerializable;
use App\Helper;

class ApiErrors implements JsonSerializable {
  private $json;
  public static $errors = [
    1 => [
      'error' => 'INTERNAL_ERROR',
      'detail' => 'There was an internal error.'
    ],
    2 => [
      'error' => 'INVALID_SURVEY',
      'detail' => 'The survey uuid given is invalid.',
      'status' => 400
    ],
    3 => [
      'error' => 'INVALID_ANSWERS_SESSION',
      'detail' => 'The answers session id given is invalid.',
      'status' => 400
    ]
  ];

  public function __construct($error_type, $error_data = []) {
    $error = ApiErrors::getErrorType($error_type) ?: ApiErrors::getErrorType('INTERNAL_ERROR');
    $this->json = [
      'error_data' => $error_data,
      'real_error' => $error_type,
      'error' => $error['error'],
      'code' => $error['code'],
      'status' => $error['status'],
      'detail' => $error['detail']
    ];

    foreach($this->json as $key => $value):
      $this->$key = $value;
    endforeach;
  }

  public static function getErrorType($error_type) {
    foreach(ApiErrors::$errors as $code => $error):
      if($error['error'] === $error_type):
        return array_merge(['code' => $code, 'status' => 500], $error);
      endif;
    endforeach;
    return false;
  }

  public function jsonSerialize() {
    return $this->json;
  }
}
