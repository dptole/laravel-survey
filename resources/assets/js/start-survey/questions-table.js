import $ from 'jquery'
import API from '../api'

export default class QuestionsTable {
  constructor(start_survey_button, survey_container, public_survey_template, public_survey_answers_template) {
    this.setStartButton(start_survey_button)
    this.setSurveyContainer(survey_container)

    this.setSurveyTemplate(public_survey_template)
    this.setSurveyAnswersTemplate(public_survey_answers_template)

    this.generateSessionId()
  }

  setSurveyContainer(survey_container) {
    this.survey_container = survey_container
  }

  setStartButton(start_survey_button) {
    this.start_survey_button = start_survey_button
    this.buttonExtractData(this.start_survey_button)
    this.buttonSetInitialState(this.start_survey_button)
  }

  setSurveyAnswersTemplate(public_survey_answers_template) {
    this.public_survey_answers_template = public_survey_answers_template
  }

  setSurveyTemplate(public_survey_template) {
    this.public_survey_template = public_survey_template
  }

  buttonSetInitialState(start_survey_button) {
    start_survey_button.addClass('disabled')
  }

  buttonExtractData(start_survey_button) {
    this.questions = start_survey_button.data('questions')
    this.survey_uuid = start_survey_button.data('surveyUuid')
  }

  buttonHandleEvents(start_survey_button) {
    start_survey_button.on('click', event => this.handleStartSurvey(event))
  }

  handleStartSurvey(event) {
    this.goToQuestion(0)
  }

  goToQuestion(number) {
    const next_question = this.questions[number]

    if(!next_question) {
      console.log(this.getSessionId())
      return alert('Send anwers.')
    }

    const template = $(document.importNode(this.public_survey_template.prop('content'), true))

    template.find('.public-survey-question-number').text(number + 1)
    template.find('.public-survey-question-description').text(next_question.description)

    template.find('.public-survey-options').append(
      next_question.answers.map(answer => {
        const answer_template = $(document.importNode(this.public_survey_answers_template.prop('content'), true)).find('.' + answer.type)
        answer_template.data('answer-uuid', answer.uuid)

        if(answer.type === 'check')
          answer_template.find('label').html(answer.description)

        answer_template.find('label').attr('for', `check-${answer.uuid}`)
        answer_template.find('input[type="radio"]').attr('id', `check-${answer.uuid}`)

        return answer_template
      })
    )

    this.survey_container.fadeOut('slow', undefined, _ => {
      this.survey_container.html('').append(template).fadeIn('slow')
      this.start_survey_button.text('Next').off().on('click', event => this.goToQuestion(number + 1))
    })
  }

  getSessionId() {
    return this._session_id
  }

  async generateSessionId() {
    this._session_id = await API.getSessionId(this.survey_uuid)
    this.start_survey_button.removeClass('disabled')
    this.buttonHandleEvents(this.start_survey_button)
  }
}
