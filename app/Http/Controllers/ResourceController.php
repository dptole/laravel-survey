<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResourceController extends Controller {
  public function js() {
    return response()->file(public_path() . '/js/app.js', [
      'content-type' => 'text/javascript'
    ]);
  }

  public function css() {
    return response()->file(public_path() . '/css/app.css', [
      'content-type' => 'text/css'
    ]);
  }
}

