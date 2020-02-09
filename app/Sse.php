<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sse extends Model
{
  public static function listen($channel, $last_event = null, $limit = 1) {
    if($last_event === null)
      $last_event = date('Y-m-d H:i:s');

    return function() use ($channel, &$last_event, $limit) {
      $events = self::where('created_at', '>', $last_event)
        ->where('channel', '=', $channel)
        ->limit($limit)
        ->get()
        ->all()
      ;

      if(count($events) === 0):
        return null;
      endif;

      $last_event = $events[0]->created_at;

      return $events[0];
    };
  }

  public static function trigger($channel, $event, $message) {
    $sse = new self;
    $sse->channel = $channel;
    $sse->event = $event;
    $sse->message = json_encode($message);
    $sse->save();
    return $sse;
  }
}
