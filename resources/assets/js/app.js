import b from 'bootstrap-sass'
import $ from 'jquery'
import _ from 'lodash'
import AnswersTable from './answers-table'

window.$ = window.jQuery = $

const answers_table = new AnswersTable('.survey-answers-table', '.survey-add-answer')
const has_errors = $('.survey-errors').length === 1
const form_survey_question = $('form#survey-form-question')
const question_options = form_survey_question.data('questionOptions')

form_survey_question.on('submit', function(event) {
  answers_table.store(form_survey_question.data('surveyUuid'))
})

if(_.isArray(question_options)) // editing
  answers_table.addAnswers(question_options)
else if(has_errors && form_survey_question.length === 1) // error
  answers_table.restore(form_survey_question.data('surveyUuid'))

if(answers_table.countRows() < 1) // default
  answers_table.addAnswer()

