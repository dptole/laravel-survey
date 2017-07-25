import $ from 'jquery'
import API from '../api'

export default class QuestionsTable {
  constructor(start_survey_button) {
    this.setStartButton(start_survey_button)
    this.generateSessionId()
  }

  setStartButton(start_survey_button) {
    this.start_survey_button = start_survey_button
    this.extractData(this.start_survey_button)
    this.handleEvents(this.start_survey_button)
  }

  extractData(start_survey_button) {
    this.questions = start_survey_button.data('questions')
    this.survey_uuid = start_survey_button.data('surveyUuid')
  }

  handleEvents(start_survey_button) {
    start_survey_button.on('click', event => this.handleStartSurvey(event))
  }

  async handleStartSurvey(event) {
    const session_id = await this.getSessionId()
    console.log('session id', session_id)
    console.log(this.questions)
  }

  getSessionId() {
    return this._session_id
  }

  generateSessionId() {
    this._session_id = API.getSessionId(this.survey_uuid)
  }
}
