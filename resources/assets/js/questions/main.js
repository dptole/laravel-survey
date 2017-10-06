import AnswersTable from './answers-table'

const answers_table = new AnswersTable({
  table_css_class: '.survey-answers-table',
  add_answer_css_class: '.survey-add-answer',
  change_order_css_class: '.survey-order-answer',
  change_order_up_css_class: '.survey-order-up-answer',
  change_order_down_css_class: '.survey-order-down-answer'
})
const has_errors = $('.survey-errors').length === 1
const form_survey_question = $('form#survey-form-question')
const question_options = form_survey_question.data('questionOptions')

form_survey_question.on('submit', _ => answers_table.store(form_survey_question.data('surveyUuid')))

if(_.isArray(question_options)) // editing
  answers_table.addAnswers(question_options)
else if(has_errors && form_survey_question.length === 1) // error
  answers_table.restore(form_survey_question.data('surveyUuid'))

if(answers_table.countRows() < 1) // default
  answers_table.addAnswer()

