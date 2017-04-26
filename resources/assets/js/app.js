import b from 'bootstrap-sass';
import $ from 'jquery';
import lodash from 'lodash'
import AnswersTable from './answers-table'

window.$ = window.jQuery = $;

const answers_table = new AnswersTable('.survey-answers-table', '.survey-add-answer');
const has_errors = $('.survey-errors').length === 1;
const form_survey_question = $('form#survey-form-question');

form_survey_question.on('submit', function(event) {
  answers_table.store(form_survey_question.data('surveyUuid'));
});

if(has_errors && form_survey_question.length === 1)
  answers_table.restore(form_survey_question.data('surveyUuid'));
else
  answers_table.addAnswer();

