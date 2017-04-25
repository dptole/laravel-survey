<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResourceController extends Controller {
  protected function getJs($filename) {
    return response()->file(public_path() . '/js/' . $filename);
  }

  public function js() {
    return $this->getJs('app.js');
  }

  public function css() {
    return response()->file(public_path() . '/css/app.css', [
      'content-type' => 'text/css'
    ]);
  }
}

