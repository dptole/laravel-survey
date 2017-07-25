import $ from 'jquery'
import QuestionsTable from './questions-table'

const start_survey_button = $('.survey-questions')
    , questions_table = new QuestionsTable(start_survey_button)
