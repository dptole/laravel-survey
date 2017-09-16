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
    this.data_survey.all_answers = []

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
    this._session_id = await API.generateSessionId(survey_uuid)
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
        this.dom_survey_table.addClass('table-hover')
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

  viewRenderAnswer(number, answer, answer_index) {
    if(answer.type === 'check')
      return this.viewRenderAnswerCheck(number, answer, answer_index)
    else if(answer.type === 'free')
      return this.viewRenderAnswerFree(number, answer, answer_index)
    return false
  }

  viewRenderAnswerFree(number, answer, answer_index) {
    function getInput(input) {
      return input.val().trim()
    }

    function validInput(input) {
      return !!getInput(input)
    }

    function nextButtonClick(event) {
      if(disableButton(validInput(input)))
        return false

      instance.selectedAnswer(number, answer_index)
      API.saveAnswer(
        instance.getSessionId(),
        instance.data_survey.all_answers[number].question.survey_id,
        instance.data_survey.all_answers[number].question.id,
        instance.data_survey.all_answers[number].answer.id,
        getInput(input)
      )
      instance.viewRenderQuestion(number + 1)
      instance.dom_start_button.off('click', nextButtonClick)
      return true
    }

    function disableButton(cond) {
      const func_name = cond ? 'removeClass' : 'addClass'
      instance.dom_start_button[func_name]('disabled')
      return func_name === 'addClass'
    }

    const input = $('<input>')
          .addClass('form-control')
          .attr('type', 'text')
          .attr('placeholder', 'Answer here...')
          .on('click focus keypress keyup keydown', event => {
            disableButton(validInput(input))
          })
        , instance = this
    this.dom_start_button.on('click', nextButtonClick)

    this.dom_survey_table.find('tbody').append(
      $('<tr>').addClass('public-survey-answer-row').append(
        $('<td>').append(
          input
        )
      )
    )

    return input
  }

  viewRenderAnswerCheck(number, answer, answer_index) {
    const button = $('<button>')
      .addClass('btn')
      .text(answer.description)
      .on('click', event => {
        this.dom_start_button.removeClass('disabled')
        this.selectedAnswer(number, answer_index)
        this.dom_start_button.on('click', event => {
          API.saveAnswer(
            this.getSessionId(),
            this.data_survey.all_answers[number].question.survey_id,
            this.data_survey.all_answers[number].question.id,
            this.data_survey.all_answers[number].answer.id
          )
          this.viewRenderQuestion(number + 1)
        })
      })

    this.dom_survey_table.find('tbody').append(
      $('<tr>').addClass('public-survey-answer-row').append(
        $('<td>').append(
          button
        )
      )
    )

    return button
  }

  viewRenderQuestion(number) {
    this.dom_start_button.addClass('disabled')
    this.dom_start_button.off()

    this.fxFade(_ => {
      const current_question = this.data_survey.all_questions[number]

      if(!current_question)
        return this.sendResults()

      this.dom_survey_title.text(`Question ${number + 1}`)
      this.dom_survey_table.find('thead tr th').text(current_question.description)
      this.dom_survey_table.find('tbody tr').remove()

      current_question.answers.forEach((answer, answer_index) => {
        this.viewRenderAnswer(number, answer, answer_index)
      })
    })
  }

  selectedAnswer(question_index, answer_index) {
    this.data_survey.all_answers[question_index] = {
      question: this.data_survey.all_questions[question_index],
      answer: this.data_survey.all_questions[question_index].answers[answer_index]
    }
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
    this.dom_survey_title.text('Thanks for participating!')
    setTimeout(_ => location = '/laravel', 2000)
  }
}
