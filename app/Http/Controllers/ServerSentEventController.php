<?php

namespace App\Http\Controllers;

use Closure;

class ServerSentEventController extends Controller {
  private function parsePayload($payload) {
    $parsed_payload = "";

    if(is_string($payload))
      $parsed_payload = $payload;

    else if(is_array($payload) && is_string($payload['data'])) {
      $lines = [];

      foreach($payload as $key => $value):
        $lines []= "$key: $value";
      endforeach;

      $parsed_payload = join("\n", $lines);
    }

    return $parsed_payload . "\n\n";
  }

  private function mainLoop(Closure $main_loop, $timeout_seconds = 2) {
    header('content-type: text/event-stream');
    $id = 0;

    while(1) {
      $payload = $main_loop($id);

      if($payload === false)
        break;

      else
        print($this->parsePayload($payload));

      if(ob_get_level() > 0) ob_end_flush();
      flush();

      if(connection_aborted()) break;

      sleep($timeout_seconds);
      $id++;
    }
  }

  public function __invoke() {
    
  }

  public function channel($channel) {
    
  }

  public function example() {
    $this->mainLoop(function($id) {
      if($id & 1)
        return 'data:{"example":"example raw json data. triggers message event"}';

      $payload = [
        'date' => date(DATE_ISO8601),
        'example' => 'example json data with date. trigger example event'
      ];

      return [
        'event' => 'example',
        'data' => json_encode($payload)
      ];
    });
  }
}
