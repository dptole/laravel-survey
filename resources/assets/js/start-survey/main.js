import $ from 'jquery'
import QuestionsTable from './questions-table'

const dom_survey_container = $('.public-survey-content')
    , questions_table = new QuestionsTable(dom_survey_container)

questions_table.start()
