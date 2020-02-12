<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

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
}
