<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    // https://laravel.com/docs/5.4/eloquent-mutators
    public function getUpdatedAtDiffAttribute()
    {
        return Helper::createCarbonDiffForHumans($this->updated_at);
    }

    public function getUpdatedAtRfc1123Attribute()
    {
        return Helper::createCarbonRfc1123String($this->updated_at);
    }

    /************************************************/

    public static function getBySurvey($q_uuid, $s_id)
    {
        return (
        $questions = self::where([
            'uuid'      => $q_uuid,
            'survey_id' => $s_id,
            'active'    => '1',
            'version'   => self::getLastVersion($s_id),
        ])->get()
      ) &&
        count($questions) === 1
      ? $questions[0]
      : null;
    }

    public static function getAllBySurveyIdPaginated($s_id)
    {
        return self::where([
            'survey_id' => $s_id,
            'active'    => '1',
            'version'   => self::getLastVersion($s_id),
        ])
      ->orderBy('updated_at', 'desc')
      ->paginate(5);
    }

    public static function getAllBySurveyIdUnpaginated($s_id)
    {
        return self::where([
            'survey_id' => $s_id,
            'active'    => '1',
            'version'   => self::getLastVersion($s_id),
        ])->orderBy('order', 'asc')->get()->all();
    }

    public static function getAllBySurveyId($s_id, $start_from = 0)
    {
        return self::where([
            'survey_id' => $s_id,
            'active'    => '1',
            'version'   => self::getLastVersion($s_id),
        ])
      ->orderBy('id', 'asc')
      ->get();
    }

    public static function getAllBySurveyIdOrdered($s_id, $start_from = 0)
    {
        return self::where([
            'survey_id' => $s_id,
            'active'    => '1',
            'version'   => self::getLastVersion($s_id),
        ])
      ->orderBy('order', 'asc')
      ->get();
    }

    public static function deleteByOwner($s_uuid, $q_uuid, $user_id)
    {
        $survey = Surveys::getByOwner($s_uuid, $user_id);
        if (!$survey) {
            return false;
        }

        $version = self::getLastVersion($survey->id);
        $query = self::where([
            'survey_id' => $survey->id,
            'uuid'      => $q_uuid,
            'active'    => '1',
            'version'   => $version,
        ])->limit(1);

        $questions = $query->get()->all();
        if (count($questions) !== 1) {
            return false;
        }

        $update_active = $query->update([
            'active' => '0',
        ]);

        if (!$update_active) {
            return false;
        }

        self::where([
            'survey_id' => $survey->id,
            'active'    => '1',
            'version'   => $version,
        ])->where(
      'order', '>', $questions[0]->order
    )->decrement(
      'order', $questions[0]->order - 1
    );

        return true;
    }

    public static function getByUuid($uuid)
    {
        return (
      $questions = self::where([
          'uuid'   => $uuid,
          'active' => '1',
      ])->get()
    ) &&
      count($questions) === 1
      ? $questions[0]
      : null;
    }

    public static function getNextInOrder($s_id)
    {
        return (
      $question = self::where([
          'survey_id' => $s_id,
          'active'    => '1',
          'version'   => self::getLastVersion($s_id),
      ])
      ->orderBy('order', 'desc')
      ->limit(1)
      ->get()
      ->all()
    ) &&
      is_array($question) &&
      count($question) === 1
      ? $question[0]->order + 1
      : 1;
    }

    public static function updateOrder($id, $order)
    {
        $question = self::find($id);
        if (!$question) {
            return false;
        }
        $question->order = $order;
        $question->save();

        return true;
    }

    public static function createQuestion($options)
    {
        $question = new self();
        $question->description = $options['description'];
        $question->uuid = $options['uuid'];
        $question->survey_id = $options['survey_id'];

        if (isset($options['version']) && Helper::isPositiveInteger($options['version'])) {
            $question->version = $options['version'];
        } else {
            $question->version = self::getLastVersion($question->survey_id);
        }

        $question->order = $options['order'];
        $question->save();

        return $question;
    }

    public static function createQuestionOptions($question, $question_options)
    {
        return QuestionsOptions::saveArray($question->id, $question_options);
    }

    public static function getLastVersion($survey_id)
    {
        $last_version = SurveysLastVersionsView::getById($survey_id);

        return $last_version ? $last_version->last_version : 1;
    }

    public static function getAllByVersion($survey_id, $version)
    {
        return array_map(function ($question) {
            $question->answers = array_map(function ($questions_answers_version) use ($question) {
                return [
                    'version' => $questions_answers_version,
                    'answers' => QuestionsOptions::getAllByVersion($question->id, $questions_answers_version),
                ];
            }, range(1, QuestionsOptionsView::getById($question->id)->last_version));

            return $question;
        }, self::where([
            'version'   => $version,
            'survey_id' => $survey_id,
            'active'    => '1',
        ])->orderBy('order')->get()->all());
    }
}
