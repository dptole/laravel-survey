<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
  use CreatesApplication;

  protected function setUp():void {
    parent::setUp();

    app('session')->setDefaultDriver('file');
  }

  # https://github.com/laravel/framework/issues/9733#issuecomment-479055459
  protected function tearDown():void {
    $instances_names = ['config', 'url', 'request', 'html', 'form', 'Illuminate\Contracts\Http\Kernel'];
    $instances = [];

    foreach($instances_names as $instance_name):
      $instances[$instance_name] = app($instance_name);
    endforeach;

    parent::tearDown();

    foreach($instances as $instance_name => $instance):
      app()->instance($instance_name, $instance);
    endforeach;
  }

  protected $fs = [];

  public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null) {
    $last_func_called = debug_backtrace()[1];
    $klass = $last_func_called['class'];

    $response = parent::call(
      $method, $uri, $parameters, $cookies, $files, $server, $content
    );

    $content = $response->content();

    if('Illuminate\Foundation\Testing\TestCase' !== $klass):
      $klass = basename(preg_replace('/\\\\/', '/', $klass));

      $func = $last_func_called['function'];

      if(!isset($this->fs[$func])):
        $this->fs[$func] = 0;
      endif;

      $this->fs[$func]++;

      $filename = $klass . '-' . $last_func_called['function'] . '-' . $this->fs[$func] . '.html';

      $pathname = dirname(__FILE__) . '/../storage/logs/' . $filename;

      file_put_contents($pathname, $content);
    endif;

    return $response;
  }
}
