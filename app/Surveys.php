<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Surveys extends Model {
  public static function getAllByOwner($user_id) {
    return Surveys::where('user_id', '=', $user_id)
      ->orderBy('updated_at', 'desc')
      ->paginate(5)
    ;
  }

  public static function getByOwner($uuid, $user_id) {
    return (
        $surveys = Surveys::where('user_id', '=', $user_id)
            ->where('uuid', '=', $uuid)
            ->limit(1)
            ->get()
        ) &&
          count($surveys) === 1
        ? $surveys[0]
        : null
    ;
  }

  public static function deleteByOwner($uuid, $user_id) {
    return Surveys::where([
      'user_id' => $user_id,
      'uuid' => $uuid
    ])->delete();
  }
}

