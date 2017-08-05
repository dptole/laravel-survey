import $ from 'jquery'
import API from '../api'
import utils from '../utils'
import _ from 'lodash'

export default class QuestionsTable {
  constructor(dom_survey_container) {
    this.dom_survey_container = dom_survey_container
    this.dom_survey_title = $('<h1>').addClass('text-center')
    this.dom_survey_title.text('Loading...')
    this.dom_survey_container.append(this.dom_survey_title)
  }

  async start() {
    this.data_survey = this.dom_survey_container.data('survey')

    try {
      await this.generateSessionId(this.data_survey.uuid)
    } catch(error) {
      const error_message = utils.goToProperty(error, 'response.data.error')
      if(error_message) this.dom_survey_title.text(error_message)
      return setTimeout(_ => location = '/laravel', 2000)
    }

    this.fxFade(_ => this.viewStart())
  }

  async generateSessionId(survey_uuid) {
    this._session_id = await API.getSessionId(survey_uuid)
  }

  getSessionId() {
    return this._session_id
  }

  viewGetTable(title) {
    const table = $('<table>').addClass('table table-bordered')
    if(_.isString(title))
      table.append(
        $('<thead>').append(
          $('<tr>').append(
            $('<th>').append(title)
          )
        )
      )
    return table
  }

  viewGetStartButton() {
    const button = $('<button>')
    return button.addClass('btn btn-block btn-success')
      .text('Start')
      .on('click', event => {
        this.viewRenderQuestion(0)
        button.text('Next')
      }
    )
  }

  viewStart() {
    this.dom_survey_title.text(`Welcome to the survey "${this.data_survey.name}"`)

    this.dom_survey_table = this.viewGetTable('Description').append(
      $('<tbody>').append(
        $('<tr>').append(
          $('<td>').append(this.data_survey.description)
        )
      )
    )

    this.dom_survey_container.append(this.dom_survey_table)
    this.dom_start_button = this.viewGetStartButton()
    this.dom_survey_container.append(this.dom_start_button)
  }

  viewRenderQuestion(number) {
    this.fxFade(_ => {
      const current_question = this.data_survey.all_questions[number]

      if(!current_question)
        return this.sendResults()

      this.dom_survey_title.text(`Question ${number + 1}`)
      this.dom_survey_table.find('thead tr th').text(current_question.description)
      this.dom_survey_table.find('tbody tr td').text('')
      this.dom_start_button.off().on('click', event => this.viewRenderQuestion(number + 1))

    })
  }

  fxFade(callback) {
    this.dom_survey_container.fadeOut('slow', undefined, x => {
      this.dom_survey_container.fadeIn('slow')
      if(_.isFunction(callback)) callback()
    })
  }

  sendResults() {
    this.dom_start_button.remove()
    this.dom_survey_table.remove()
    this.dom_survey_title.text('Uploading answers...')
  }
}
