<?php

namespace App\Http\Controllers;

use App\Answers;
use App\AnswersSessions;
use App\ApiErrors;
use App\Helper;
use App\Questions;
use App\QuestionsOptions;
use App\Surveys;
use App\SurveysLastVersionsView;
use Illuminate\Http\Request;

class APIController extends Controller
{
    /**
     * Generates a session id so that the users are tracked when answering the questions.
     *
     * @return {"success":{"session_id":"82e55c09-54e1-4628-bb7e-92a7580c4273"}}
     */
    public function getSessionId($s_uuid, Request $request)
    {
        $survey = Surveys::getByUuid($s_uuid);

        if (!$survey) {
            return response(new ApiErrors('INVALID_SURVEY', $s_uuid));
        }

        $request_info = json_encode([
            'js'      => $request->input(),
            'headers' => $request->header(),
            'server'  => $_SERVER,
            'ips'     => $request->ips(),
        ]);

        $session_id = AnswersSessions::createSession(
            $survey->id,
            $request_info
        );

        return response()->json([
            'session_id' => $session_id,
        ]);
    }

    /**
     * Saves which answer to which question on which survey the given user have chosen.
     *
     * @return {"success":true}
     */
    public function saveSurveyAnswer(Request $request)
    {
        $request_info = json_encode([
            'headers' => $request->header(),
            'ips'     => $request->ips(),
        ]);

        $answer_input = [
            null,
            $request->input('survey_id'),
            $request->input('question_id'),
            $request->input('question_option_id'),
            $request->input('free_text'),
            $request_info,
            AnswersSessions::getIdByUuid($request->input('answers_session_id')),
        ];

        list($session_id, $survey_id, $question_id, $question_option_id, $free_text, $request_info, $answers_session_id) = $answer_input;

        $answer = new Answers();
        $answer->survey_id = $survey_id;
        $answer->question_id = $question_id;
        $answer->question_option_id = $question_option_id;
        $answer->free_text = is_string($free_text) ? $free_text : '';
        $answer->request_info = $request_info;
        $answer->answers_session_id = $answers_session_id;
        $answer->save();

        $survey_uuid = Surveys::find($survey_id)->uuid;

        Helper::broadcast('public-survey-'.$survey_uuid, 'user-answer', [
            'user' => [
                'session_id'      => $request->input('answers_session_id'),
                'survey_version'  => SurveysLastVersionsView::getById($survey_id),
                'question'        => Questions::find($question_id),
                'question_option' => QuestionsOptions::find($question_option_id),
                'answer'          => $answer,
            ],
        ]);

        return response()->json(true);
    }

    /**
     * Fetches the country info via some IP.
     *
     * @return {
     *           "success": {
     *           "Address type": "IPv4 ",
     *           "Hostname": "dynamic-adsl-78-15-117-251.clienti.tiscali.it",
     *           "ASN": "8612 - TISCALI",
     *           "ISP": "Tiscali SpA",
     *           "Connection type": "xDSL",
     *           "Crawler": "No",
     *           "Proxy": "No",
     *           "Attack source": "No",
     *           "Threat level": "Low",
     *           "Country": "Italy",
     *           "State / Region": "Sardinia (Roman province)",
     *           "District / County": "Provincia di Cagliari",
     *           "City": "Casteddu/Cagliari",
     *           "Coordinates": "39.2703, 9.09582",
     *           "Timezone": "Europe/Rome (UTC+1)",
     *           "Languages": "it-IT, de-IT, fr-IT, sc, ca, co, sl",
     *           "Currency": "Euro (EUR)",
     *           "Elapsed": 9163,
     *           "Ip": "78.15.117.251"
     *           }
     *           }
     */
    public function fetchCountryInfo(Request $request)
    {
        if ($request->input('answers_session_uuid')) {
            $answers_session_id = AnswersSessions::getIdByUuid($request->input('answers_session_uuid'));
        } else {
            $answers_session_id = $request->input('answers_session_id');
        }

        $country_info = AnswersSessions::updateCountryInfo($answers_session_id, $request->input('ip'));

        if (!$country_info) {
            return response(new ApiErrors('INVALID_ANSWERS_SESSION', $request));
        }

        return response()->json($country_info);
    }
}
