import $ from 'jquery'
import QuestionsTable from './questions-table'

const start_survey_button = $('.survey-questions')
    , public_survey_content = $('.public-survey-content')
    , public_survey_template = $('#public_survey_template')
    , public_survey_answers_template = $('#public_survey_answers_template')
    , questions_table = new QuestionsTable(start_survey_button, public_survey_content, public_survey_template, public_survey_answers_template)
