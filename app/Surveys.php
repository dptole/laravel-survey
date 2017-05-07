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

  const ERR_RUN_SURVEY_OK = 0;
  const ERR_RUN_SURVEY_NOT_FOUND = 1;
  const ERR_RUN_SURVEY_INVALID_STATUS = 2;
  const ERR_RUN_SURVEY_ALREADY_RUNNING = 3;
  public static function run($uuid, $user_id) {
    $survey = Surveys::getByOwner($uuid, $user_id);

    if(!$survey):
      return Surveys::ERR_RUN_SURVEY_NOT_FOUND;
    elseif($survey->status !== 'draft'):
      return Surveys::ERR_RUN_SURVEY_INVALID_STATUS;
    elseif($survey->status === 'ready'):
      return Surveys::ERR_RUN_SURVEY_ALREADY_RUNNING;
    endif;

    $survey->status = 'ready';
    $survey->save();

    return Surveys::ERR_RUN_SURVEY_OK;
  }

  const ERR_PAUSE_SURVEY_OK = 0;
  const ERR_PAUSE_SURVEY_NOT_FOUND = 1;
  const ERR_PAUSE_SURVEY_INVALID_STATUS = 2;
  const ERR_PAUSE_SURVEY_ALREADY_PAUSED = 3;
  public static function pause($uuid, $user_id) {
    $survey = Surveys::getByOwner($uuid, $user_id);

    if(!$survey):
      return Surveys::ERR_PAUSE_SURVEY_NOT_FOUND;
    elseif($survey->status !== 'ready'):
      return Surveys::ERR_PAUSE_SURVEY_INVALID_STATUS;
    elseif($survey->status === 'draft'):
      return Surveys::ERR_PAUSE_SURVEY_ALREADY_PAUSED;
    endif;

    $survey->status = 'draft';
    $survey->save();

    return Surveys::ERR_PAUSE_SURVEY_OK;
  }
}

